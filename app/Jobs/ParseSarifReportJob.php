<?php

namespace App\Jobs;

use App\Enums\ReportFailureReason;
use App\Enums\ReportStatus;
use App\Exceptions\SarifParsingException;
use App\Models\Report;
use App\Services\Sarif\FindingFingerprint;
use App\Services\Sarif\SarifFileInspector;
use App\Services\Sarif\SarifSeverityNormalizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JsonMachine\Exception\PathNotFoundException;
use JsonMachine\Exception\SyntaxErrorException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use Throwable;

/**
 * Parses an uploaded SARIF file in streaming mode and stores its findings.
 *
 * The file is read twice, without ever loading it fully in memory:
 * 1. A bounded pass over the driver metadata and its rules (hundreds at most).
 * 2. A streaming pass over runs[0].results, inserting findings in batches.
 *
 * Scope (v1): only runs[0] of each file is processed. Files with multiple runs
 * are intentionally out of scope; see docs/scope once the documentation phase
 * lands. codeFlows is preserved as-is inside each finding payload and never
 * normalized into relational tables.
 */
class ParseSarifReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Findings written on a single database round trip.
     */
    private int $chunkSize;

    /**
     * Create a new job instance.
     *
     * @param  string  $storedPath  Path of the uploaded file relative to the configured disk.
     */
    public function __construct(
        public readonly Report $report,
        public readonly string $storedPath,
    ) {
        $this->chunkSize = max(1, (int) config('clarif.parsing.chunk_size', 500));
    }

    /**
     * Execute the job.
     */
    public function handle(
        SarifFileInspector $inspector,
        SarifSeverityNormalizer $severityNormalizer,
        FindingFingerprint $fingerprint,
    ): void {
        $report = $this->report;
        $report->update(['status' => ReportStatus::Processing, 'error_message' => null]);

        $startedAt = microtime(true);

        Log::channel('sarif')->info('SARIF parsing started.', [
            'report_id' => $report->id,
            'filename' => $report->original_filename,
            'stored_path' => $this->storedPath,
        ]);

        try {
            $path = Storage::disk(config('clarif.uploads.disk'))->path($this->storedPath);

            $inspector->assertSupportedVersion($path);

            $this->forgetPreviousFindings($report);

            $totalFindings = $this->processDriverAndResults($path, $report, $severityNormalizer, $fingerprint);

            $report->update([
                'status' => ReportStatus::Completed,
                'error_message' => null,
                'meta' => array_merge($report->meta ?? [], ['total_findings' => $totalFindings]),
            ]);

            Log::channel('sarif')->info('SARIF parsing completed.', [
                'report_id' => $report->id,
                'total_findings' => $totalFindings,
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ]);
        } catch (SarifParsingException $e) {
            $this->markAsFailed($report, $e);
        } catch (Throwable $e) {
            report($e);

            $this->markAsFailed($report, new SarifParsingException(
                ReportFailureReason::Unknown,
                $e->getMessage(),
                $e,
            ));
        }
    }

    /**
     * Read the driver and stream its results into the findings table.
     */
    private function processDriverAndResults(
        string $path,
        Report $report,
        SarifSeverityNormalizer $severityNormalizer,
        FindingFingerprint $fingerprint,
    ): int {
        $driver = $this->readObject($path, '/runs/0/tool/driver');

        $toolName = is_string($driver['name'] ?? null) ? $driver['name'] : config('clarif.unknown_tool_name');
        $toolVersion = is_string($driver['version'] ?? null)
            ? $driver['version']
            : (is_string($driver['semanticVersion'] ?? null) ? $driver['semanticVersion'] : null);

        $report->update([
            'tool_name' => $toolName,
            'tool_driver_version' => $toolVersion,
        ]);

        $this->warnOnUnknownDriver($report, $toolName);

        $rules = $this->indexRules($driver['rules'] ?? []);

        return $this->storeFindings($path, $report, $rules, $severityNormalizer, $fingerprint);
    }

    /**
     * Stream runs[0].results and insert the normalized findings in chunks.
     *
     * @param  array<string, string|null>  $rules
     */
    private function storeFindings(
        string $path,
        Report $report,
        array $rules,
        SarifSeverityNormalizer $severityNormalizer,
        FindingFingerprint $fingerprint,
    ): int {
        $total = 0;
        $chunk = [];

        try {
            $results = $this->decode($path, '/runs/0/results');

            foreach ($results as $result) {
                if (! is_array($result)) {
                    continue;
                }

                $chunk[] = $this->buildRow($result, $report->id, $rules, $severityNormalizer, $fingerprint);
                $total++;

                if (count($chunk) >= $this->chunkSize) {
                    $this->insertChunk($chunk);
                    $chunk = [];
                }
            }
        } catch (PathNotFoundException) {
            // A run without a "results" key is valid SARIF: it simply has no findings.
            return 0;
        } catch (SyntaxErrorException $e) {
            throw SarifParsingException::invalidJson($e);
        }

        if ($chunk !== []) {
            $this->insertChunk($chunk);
        }

        return $total;
    }

    /**
     * Build a query-builder row from a decoded SARIF result.
     *
     * @param  array<string, mixed>  $result
     * @param  array<string, string|null>  $rules
     * @return array<string, mixed>
     */
    private function buildRow(
        array $result,
        int $reportId,
        array $rules,
        SarifSeverityNormalizer $severityNormalizer,
        FindingFingerprint $fingerprint,
    ): array {
        $ruleId = is_string($result['ruleId'] ?? null) ? $result['ruleId'] : '';
        $location = $result['locations'][0]['physicalLocation'] ?? [];
        $filePath = is_string($location['artifactLocation']['uri'] ?? null)
            ? $location['artifactLocation']['uri']
            : '';

        $startLine = $location['region']['startLine'] ?? null;
        $line = is_numeric($startLine) ? (int) $startLine : null;

        $message = $result['message']['text']
            ?? $result['message']['markdown']
            ?? '';

        $severity = $severityNormalizer->normalize($result, $rules);
        $now = now();

        return [
            'report_id' => $reportId,
            'rule_id' => mb_substr($ruleId, 0, 255),
            'file_path' => mb_substr($filePath, 0, 1024),
            'line' => $line,
            'severity' => $severity->value,
            'message' => is_string($message) ? $message : '',
            'fingerprint' => $fingerprint->generate($ruleId, $filePath, $line),
            'payload' => json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * Insert a batch of findings inside its own transaction.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function insertChunk(array $rows): void
    {
        DB::transaction(function () use ($rows): void {
            DB::table('findings')->insert($rows);
        });
    }

    /**
     * Remove findings from a previous attempt so retries stay idempotent.
     */
    private function forgetPreviousFindings(Report $report): void
    {
        DB::table('findings')->where('report_id', $report->id)->delete();
    }

    /**
     * Open a streaming iterator for a JSON Pointer, decoding to arrays.
     */
    private function decode(string $path, string $pointer): Items
    {
        return Items::fromFile($path, [
            'pointer' => $pointer,
            'decoder' => new ExtJsonDecoder(true),
        ]);
    }

    /**
     * Read the selected JSON Pointer as a bounded associative array.
     *
     * @return array<string, mixed>
     */
    private function readObject(string $path, string $pointer): array
    {
        try {
            return iterator_to_array($this->decode($path, $pointer));
        } catch (PathNotFoundException $e) {
            throw SarifParsingException::missingRuns($e);
        } catch (SyntaxErrorException $e) {
            throw SarifParsingException::invalidJson($e);
        }
    }

    /**
     * Flatten the declared rules into a ruleId => default level map.
     *
     * @return array<string, string|null>
     */
    private function indexRules(mixed $rules): array
    {
        if (! is_array($rules)) {
            return [];
        }

        $index = [];

        foreach ($rules as $rule) {
            if (! is_array($rule) || ! is_string($rule['id'] ?? null)) {
                continue;
            }

            $level = $rule['defaultConfiguration']['level'] ?? null;

            $index[$rule['id']] = is_string($level) ? $level : null;
        }

        return $index;
    }

    /**
     * Emit a warning when the file comes from a driver Clarif does not know.
     */
    private function warnOnUnknownDriver(Report $report, string $toolName): void
    {
        $known = array_map('strtolower', config('clarif.known_drivers', []));

        if (! in_array(strtolower($toolName), $known, true)) {
            Log::channel('sarif')->warning('Unrecognized SARIF driver.', [
                'report_id' => $report->id,
                'tool_name' => $toolName,
            ]);
        }
    }

    /**
     * Log the technical failure and store the translatable message on the report.
     */
    private function markAsFailed(Report $report, SarifParsingException $e): void
    {
        Log::channel('sarif')->error('SARIF parsing failed.', [
            'report_id' => $report->id,
            'reason' => $e->reason->value,
            'exception' => $e,
        ]);

        $report->update([
            'status' => ReportStatus::Failed,
            'error_message' => $e->reason->label(),
            'meta' => array_merge($report->meta ?? [], ['failure_reason' => $e->reason->value]),
        ]);
    }
}

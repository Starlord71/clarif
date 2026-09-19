<?php

namespace App\Services\Sarif;

use App\Contracts\FindingRepositoryInterface;
use App\Contracts\ReportRepositoryInterface;
use App\Enums\ReportFailureReason;
use App\Enums\ReportStatus;
use App\Exceptions\SarifParsingException;
use App\Models\Report;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JsonMachine\Exception\PathNotFoundException;
use JsonMachine\Exception\SyntaxErrorException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use Throwable;

/**
 * Business logic for turning an uploaded SARIF file into stored findings.
 *
 * The file is read twice, without ever loading it fully in memory:
 * 1. A bounded pass over the driver metadata and its rules (hundreds at most).
 * 2. A streaming pass over runs[0].results, inserting findings in batches.
 *
 * Scope (v1): only runs[0] of each file is processed. Files with multiple runs
 * are intentionally out of scope; see docs/architecture.md. codeFlows is
 * preserved as-is inside each finding payload and never normalized into
 * relational tables.
 *
 * Persistence goes exclusively through the repository contracts, so this
 * service can be unit tested with in-memory fakes and knows nothing about SQL.
 */
final class SarifIngestionService
{
    /**
     * Findings written on a single database round trip.
     */
    private int $chunkSize;

    /**
     * Create a new ingestion service.
     */
    public function __construct(
        private readonly ReportRepositoryInterface $reports,
        private readonly FindingRepositoryInterface $findings,
        private readonly SarifFileInspector $inspector,
        private readonly SarifSeverityNormalizer $severityNormalizer,
        private readonly FindingFingerprint $fingerprint,
    ) {
        $this->chunkSize = max(1, (int) config('clarif.parsing.chunk_size', 500));
    }

    /**
     * Parse the uploaded file and persist its findings on the given report.
     *
     * All domain failures are captured here and reflected on the report status
     * so the queue job always finishes cleanly.
     */
    public function ingest(Report $report, string $storedPath): void
    {
        $report = $this->reports->update($report, ['status' => ReportStatus::Processing, 'error_message' => null]);

        $startedAt = microtime(true);

        Log::channel('sarif')->info('SARIF parsing started.', [
            'report_id' => $report->id,
            'filename' => $report->original_filename,
            'stored_path' => $storedPath,
        ]);

        try {
            $path = Storage::disk(config('clarif.uploads.disk'))->path($storedPath);

            $this->inspector->assertSupportedVersion($path);

            $this->findings->deleteForReport($report);

            $totalFindings = $this->processDriverAndResults($path, $report);

            $report = $this->reports->update($report, [
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
    private function processDriverAndResults(string $path, Report $report): int
    {
        $driver = $this->readObject($path, '/runs/0/tool/driver');

        $toolName = is_string($driver['name'] ?? null) ? $driver['name'] : config('clarif.unknown_tool_name');
        $toolVersion = is_string($driver['version'] ?? null)
            ? $driver['version']
            : (is_string($driver['semanticVersion'] ?? null) ? $driver['semanticVersion'] : null);

        $this->reports->update($report, [
            'tool_name' => $toolName,
            'tool_driver_version' => $toolVersion,
        ]);

        $this->warnOnUnknownDriver($report, $toolName);

        $rules = $this->indexRules($driver['rules'] ?? []);

        return $this->storeFindings($path, $report, $rules);
    }

    /**
     * Stream runs[0].results and insert the normalized findings in chunks.
     *
     * @param  array<string, string|null>  $rules
     */
    private function storeFindings(string $path, Report $report, array $rules): int
    {
        $total = 0;
        $chunk = [];

        try {
            $results = $this->decode($path, '/runs/0/results');

            foreach ($results as $result) {
                if (! is_array($result)) {
                    continue;
                }

                $chunk[] = $this->buildRow($result, $report->id, $rules);
                $total++;

                if (count($chunk) >= $this->chunkSize) {
                    $this->findings->insertBatch($chunk);
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
            $this->findings->insertBatch($chunk);
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
    private function buildRow(array $result, int $reportId, array $rules): array
    {
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

        $severity = $this->severityNormalizer->normalize($result, $rules);
        $now = now();

        return [
            'report_id' => $reportId,
            'rule_id' => mb_substr($ruleId, 0, 255),
            'file_path' => mb_substr($filePath, 0, 1024),
            'line' => $line,
            'severity' => $severity->value,
            'message' => is_string($message) ? $message : '',
            'fingerprint' => $this->fingerprint->generate($ruleId, $filePath, $line),
            'payload' => json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
            'created_at' => $now,
            'updated_at' => $now,
        ];
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

        $this->reports->update($report, [
            'status' => ReportStatus::Failed,
            'error_message' => $e->reason->label(),
            'meta' => array_merge($report->meta ?? [], ['failure_reason' => $e->reason->value]),
        ]);
    }
}

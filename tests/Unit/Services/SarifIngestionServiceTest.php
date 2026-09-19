<?php

namespace Tests\Unit\Services;

use App\Enums\ReportFailureReason;
use App\Enums\ReportStatus;
use App\Enums\SarifLevel;
use App\Models\Report;
use App\Services\Sarif\FindingFingerprint;
use App\Services\Sarif\SarifFileInspector;
use App\Services\Sarif\SarifIngestionService;
use App\Services\Sarif\SarifSeverityNormalizer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\Support\FakeFindingRepository;
use Tests\Support\FakeReportRepository;
use Tests\TestCase;

/**
 * Unit tests for the ingestion service using in-memory repository fakes.
 *
 * The database is never touched: the repository contracts are replaced by
 * recording fakes, which proves the parsing logic is decoupled from SQL.
 */
class SarifIngestionServiceTest extends TestCase
{
    private FakeReportRepository $reports;

    private FakeFindingRepository $findings;

    private SarifIngestionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reports = new FakeReportRepository;
        $this->findings = new FakeFindingRepository;

        $this->service = new SarifIngestionService(
            $this->reports,
            $this->findings,
            $this->app->make(SarifFileInspector::class),
            new SarifSeverityNormalizer,
            new FindingFingerprint,
        );
    }

    /**
     * Store a fixture on the fake disk and return a report pointing at it.
     */
    private function reportFor(string $fixture): Report
    {
        return $this->reportForContents(
            $fixture,
            (string) file_get_contents(base_path('tests/Fixtures/sarif/'.$fixture)),
        );
    }

    /**
     * Store arbitrary SARIF contents on the fake disk and return a report.
     */
    private function reportForContents(string $name, string $contents): Report
    {
        config(['clarif.uploads.disk' => 'sarif-ingestion']);

        Storage::fake('sarif-ingestion');
        Storage::disk('sarif-ingestion')->put($name, $contents);

        $report = new Report(['original_filename' => $name, 'tool_name' => 'unknown']);
        $report->setAttribute('id', 1);

        return $report;
    }

    public function test_it_streams_normalized_findings_through_the_repository(): void
    {
        $report = $this->reportFor('eslint-style.sarif');

        $this->service->ingest($report, 'eslint-style.sarif');

        $this->assertSame(ReportStatus::Completed, $report->status);
        $this->assertSame('ESLint', $report->tool_name);
        $this->assertSame('8.57.0', $report->tool_driver_version);
        $this->assertSame(4, $report->meta['total_findings']);
        $this->assertSame(1, $this->findings->deleteCalls);

        $rows = collect($this->findings->allRows())->keyBy('rule_id');

        $this->assertCount(4, $rows);
        $this->assertSame(SarifLevel::Error->value, $rows['no-unused-vars']['severity']);
        $this->assertSame(SarifLevel::Note->value, $rows['no-console']['severity']);
        $this->assertSame(SarifLevel::Warning->value, $rows['eqeqeq']['severity']);
        $this->assertSame(SarifLevel::Warning->value, $rows['unknown-rule']['severity']);

        $finding = $rows['no-unused-vars'];

        $this->assertSame('src/index.js', $finding['file_path']);
        $this->assertSame(12, $finding['line']);
        $this->assertSame(hash('sha256', 'no-unused-vars|src/index.js|12'), $finding['fingerprint']);
        $this->assertStringContainsString('codeFlows', (string) $finding['payload']);
    }

    public function test_it_marks_the_report_as_failed_without_writing_rows_on_invalid_json(): void
    {
        $report = $this->reportFor('malformed.sarif');

        $this->service->ingest($report, 'malformed.sarif');

        $this->assertSame(ReportStatus::Failed, $report->status);
        $this->assertSame(ReportFailureReason::InvalidJson->label(), $report->error_message);
        $this->assertSame(ReportFailureReason::InvalidJson->value, $report->meta['failure_reason']);
        $this->assertSame(0, $this->findings->deleteCalls);
        $this->assertSame([], $this->findings->allRows());
    }

    public function test_it_batches_findings_using_the_configured_chunk_size(): void
    {
        $results = [];

        for ($i = 0; $i < 501; $i++) {
            $results[] = [
                'ruleId' => 'rule-'.$i,
                'message' => ['text' => 'Finding '.$i],
                'locations' => [
                    [
                        'physicalLocation' => [
                            'artifactLocation' => ['uri' => 'src/file-'.$i.'.js'],
                            'region' => ['startLine' => $i + 1],
                        ],
                    ],
                ],
            ];
        }

        $report = $this->reportForContents('large.sarif', (string) json_encode([
            'version' => '2.1.0',
            'runs' => [
                [
                    'tool' => ['driver' => ['name' => 'ESLint', 'version' => '8.57.0', 'rules' => []]],
                    'results' => $results,
                ],
            ],
        ]));

        $this->service->ingest($report, 'large.sarif');

        $this->assertSame(ReportStatus::Completed, $report->status);
        $this->assertSame(501, $report->meta['total_findings']);
        $this->assertCount(2, $this->findings->batches);
        $this->assertCount(500, $this->findings->batches[0]);
        $this->assertCount(1, $this->findings->batches[1]);
        $this->assertCount(501, $this->findings->allRows());
    }

    public function test_it_warns_when_the_driver_is_not_known(): void
    {
        Log::shouldReceive('channel')->with('sarif')->andReturnSelf();
        Log::shouldReceive('info');
        Log::shouldReceive('error');
        Log::shouldReceive('warning')
            ->once()
            ->with('Unrecognized SARIF driver.', Mockery::on(
                fn (array $context): bool => $context['tool_name'] === 'MysteryTool' && $context['report_id'] === 1,
            ));

        $report = $this->reportForContents('mystery.sarif', (string) json_encode([
            'version' => '2.1.0',
            'runs' => [
                [
                    'tool' => ['driver' => ['name' => 'MysteryTool', 'version' => '1.0.0', 'rules' => []]],
                    'results' => [],
                ],
            ],
        ]));

        $this->service->ingest($report, 'mystery.sarif');

        $this->assertSame('MysteryTool', $report->tool_name);
        $this->assertSame(ReportStatus::Completed, $report->status);
    }

    public function test_it_falls_back_to_semantic_version_when_version_is_missing(): void
    {
        $report = $this->reportForContents('semver.sarif', (string) json_encode([
            'version' => '2.1.0',
            'runs' => [
                [
                    'tool' => ['driver' => ['name' => 'ESLint', 'semanticVersion' => '9.9.9', 'rules' => []]],
                    'results' => [],
                ],
            ],
        ]));

        $this->service->ingest($report, 'semver.sarif');

        $this->assertSame(ReportStatus::Completed, $report->status);
        $this->assertSame('9.9.9', $report->tool_driver_version);
    }
}

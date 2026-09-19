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
use Illuminate\Support\Facades\Storage;
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
        config(['clarif.uploads.disk' => 'sarif-ingestion']);

        Storage::fake('sarif-ingestion');
        Storage::disk('sarif-ingestion')->put($fixture, (string) file_get_contents(base_path('tests/Fixtures/sarif/'.$fixture)));

        $report = new Report(['original_filename' => $fixture, 'tool_name' => 'unknown']);
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
}

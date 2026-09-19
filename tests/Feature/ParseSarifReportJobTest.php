<?php

namespace Tests\Feature;

use App\Enums\ReportFailureReason;
use App\Enums\ReportStatus;
use App\Enums\SarifLevel;
use App\Jobs\ParseSarifReportJob;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature tests for the streaming SARIF parsing job.
 */
class ParseSarifReportJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Store a fixture as an upload and run the job synchronously.
     */
    private function parseFixture(string $fixture): Report
    {
        Storage::fake('sarif');

        $path = $fixture;
        Storage::disk('sarif')->put($path, (string) file_get_contents(base_path('tests/Fixtures/sarif/'.$fixture)));

        $report = Report::create([
            'original_filename' => $fixture,
            'tool_name' => 'unknown',
            'status' => ReportStatus::Pending,
        ]);

        ParseSarifReportJob::dispatchSync($report, $path);

        return $report->refresh();
    }

    public function test_it_parses_results_and_normalizes_severities(): void
    {
        $report = $this->parseFixture('eslint-style.sarif');

        $this->assertSame(ReportStatus::Completed, $report->status);
        $this->assertSame('ESLint', $report->tool_name);
        $this->assertSame('8.57.0', $report->tool_driver_version);
        $this->assertSame(4, $report->meta['total_findings']);
        $this->assertCount(4, $report->findings);

        $byRule = $report->findings->keyBy('rule_id');

        $this->assertSame(SarifLevel::Error, $byRule['no-unused-vars']->severity);
        $this->assertSame(SarifLevel::Note, $byRule['no-console']->severity);
        $this->assertSame(SarifLevel::Warning, $byRule['eqeqeq']->severity);
        $this->assertSame(SarifLevel::Warning, $byRule['unknown-rule']->severity);

        $finding = $byRule['no-unused-vars'];

        $this->assertSame('src/index.js', $finding->file_path);
        $this->assertSame(12, $finding->line);
        $this->assertSame(hash('sha256', 'no-unused-vars|src/index.js|12'), $finding->fingerprint);
        $this->assertArrayHasKey('codeFlows', $finding->payload);
    }

    public function test_malformed_json_marks_the_report_as_failed_with_a_friendly_message(): void
    {
        $report = $this->parseFixture('malformed.sarif');

        $this->assertSame(ReportStatus::Failed, $report->status);
        $this->assertSame(ReportFailureReason::InvalidJson->label(), $report->error_message);
        $this->assertSame(0, $report->findings()->count());
    }

    public function test_a_file_without_runs_is_reported_as_missing_runs(): void
    {
        $report = $this->parseFixture('missing-runs.sarif');

        $this->assertSame(ReportStatus::Failed, $report->status);
        $this->assertSame(ReportFailureReason::MissingRuns->label(), $report->error_message);
    }

    public function test_an_unsupported_version_marks_the_report_as_failed(): void
    {
        $report = $this->parseFixture('unsupported-version.sarif');

        $this->assertSame(ReportStatus::Failed, $report->status);
        $this->assertSame(ReportFailureReason::UnsupportedVersion->label(), $report->error_message);
    }
}

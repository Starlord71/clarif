<?php

namespace Tests\Feature;

use App\Enums\ReportFailureReason;
use App\Enums\ReportStatus;
use App\Jobs\ParseSarifReportJob;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature tests for the SARIF upload endpoint.
 */
class ReportUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_valid_sarif_upload_creates_a_report_and_dispatches_the_job(): void
    {
        Queue::fake();
        Storage::fake('sarif');

        $file = UploadedFile::fake()->createWithContent(
            'eslint-style.sarif',
            (string) file_get_contents(base_path('tests/Fixtures/sarif/eslint-style.sarif')),
        );

        $response = $this->post(route('reports.store'), ['report' => $file]);

        $report = Report::sole();

        $response->assertRedirect(route('reports.show', $report));
        $this->assertSame(ReportStatus::Pending, $report->status);
        $this->assertSame('eslint-style.sarif', $report->original_filename);

        $stored = Storage::disk('sarif')->allFiles();
        $this->assertCount(1, $stored);
        $this->assertStringEndsWith('.json', $stored[0]);
        Queue::assertPushed(ParseSarifReportJob::class);
    }

    public function test_the_upload_rejects_an_unsupported_extension(): void
    {
        Queue::fake();
        Storage::fake('sarif');

        $file = UploadedFile::fake()->createWithContent('notes.txt', '{}');

        $response = $this->from('/')->post(route('reports.store'), ['report' => $file]);

        $response->assertSessionHasErrors('report');
        $this->assertSame(0, Report::count());
        Queue::assertNothingPushed();
    }

    public function test_the_upload_rejects_a_php_file_disguised_as_sarif(): void
    {
        Queue::fake();
        Storage::fake('sarif');

        $file = UploadedFile::fake()->createWithContent(
            'payload.sarif',
            "<?php echo shell_exec('whoami'); ?>",
        );

        $response = $this->from('/')->post(route('reports.store'), ['report' => $file]);

        $response->assertSessionHasErrors([
            'report' => ReportFailureReason::InvalidJson->label(),
        ]);
        $this->assertSame(0, Report::count());
        Queue::assertNothingPushed();
        $this->assertSame([], Storage::disk('sarif')->allFiles());
    }

    public function test_the_upload_rejects_malformed_json_before_persisting(): void
    {
        Queue::fake();
        Storage::fake('sarif');

        $file = UploadedFile::fake()->createWithContent(
            'malformed.sarif',
            (string) file_get_contents(base_path('tests/Fixtures/sarif/malformed.sarif')),
        );

        $response = $this->from('/')->post(route('reports.store'), ['report' => $file]);

        $response->assertSessionHasErrors([
            'report' => ReportFailureReason::InvalidJson->label(),
        ]);
        $this->assertSame(0, Report::count());
        Queue::assertNothingPushed();
    }

    public function test_the_upload_rejects_a_native_tool_json_that_is_not_sarif(): void
    {
        Queue::fake();
        Storage::fake('sarif');

        $file = UploadedFile::fake()->createWithContent(
            'semgrep-report.json',
            (string) file_get_contents(base_path('tests/Fixtures/sarif/native-tool-report.json')),
        );

        $response = $this->from('/')->post(route('reports.store'), ['report' => $file]);

        $response->assertSessionHasErrors([
            'report' => ReportFailureReason::NotSarif->label(),
        ]);
        $this->assertSame(0, Report::count());
        Queue::assertNothingPushed();
        $this->assertSame([], Storage::disk('sarif')->allFiles());
    }

    public function test_the_upload_rejects_an_unsupported_sarif_version_before_persisting(): void
    {
        Queue::fake();
        Storage::fake('sarif');

        $file = UploadedFile::fake()->createWithContent(
            'unsupported-version.sarif',
            (string) file_get_contents(base_path('tests/Fixtures/sarif/unsupported-version.sarif')),
        );

        $response = $this->from('/')->post(route('reports.store'), ['report' => $file]);

        $response->assertSessionHasErrors([
            'report' => ReportFailureReason::UnsupportedVersion->label(),
        ]);
        $this->assertSame(0, Report::count());
        Queue::assertNothingPushed();
    }
}

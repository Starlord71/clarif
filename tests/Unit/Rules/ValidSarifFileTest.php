<?php

namespace Tests\Unit\Rules;

use App\Enums\ReportFailureReason;
use App\Rules\ValidSarifFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

/**
 * Unit tests for the content-based SARIF upload validation rule.
 *
 * When the rule rejects a file it must log only metadata (filename, size and
 * reason) to the dedicated "sarif" channel, never the file contents, and it
 * must surface the categorized reason through the fail callback.
 */
class ValidSarifFileTest extends TestCase
{
    /**
     * Run the rule against an in-memory upload and capture the failure message.
     */
    private function applyRule(string $filename, string $contents): ?string
    {
        $file = UploadedFile::fake()->createWithContent($filename, $contents);
        $failure = null;

        (new ValidSarifFile)->validate('report', $file, function (string $message) use (&$failure): void {
            $failure = $message;
        });

        return $failure;
    }

    public function test_it_logs_only_metadata_when_rejecting_a_native_tool_export(): void
    {
        Log::shouldReceive('channel')->with('sarif')->andReturnSelf();
        Log::shouldReceive('warning')
            ->once()
            ->with('SARIF upload rejected by preflight check.', Mockery::on(function (array $context): bool {
                return $context['filename'] === 'semgrep-report.json'
                    && $context['reason'] === ReportFailureReason::NotSarif->value
                    && is_int($context['size'])
                    && $context['size'] > 0
                    && ! array_key_exists('contents', $context);
            }));

        $contents = (string) file_get_contents(base_path('tests/Fixtures/sarif/native-tool-report.json'));

        $this->assertSame(
            ReportFailureReason::NotSarif->label(),
            $this->applyRule('semgrep-report.json', $contents),
        );
    }

    public function test_it_logs_the_invalid_json_reason_without_the_contents(): void
    {
        Log::shouldReceive('channel')->with('sarif')->andReturnSelf();
        Log::shouldReceive('warning')
            ->once()
            ->with('SARIF upload rejected by preflight check.', Mockery::on(function (array $context): bool {
                return $context['filename'] === 'broken.sarif'
                    && $context['reason'] === ReportFailureReason::InvalidJson->value;
            }));

        $this->assertSame(
            ReportFailureReason::InvalidJson->label(),
            $this->applyRule('broken.sarif', '{"version": "2.1.0",'),
        );
    }

    public function test_it_accepts_a_valid_sarif_file_without_logging_anything(): void
    {
        Log::shouldReceive('channel')->never();
        Log::shouldReceive('warning')->never();

        $contents = (string) file_get_contents(base_path('tests/Fixtures/sarif/eslint-style.sarif'));

        $this->assertNull($this->applyRule('eslint-style.sarif', $contents));
    }
}

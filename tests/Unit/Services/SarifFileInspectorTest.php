<?php

namespace Tests\Unit\Services;

use App\Enums\ReportFailureReason;
use App\Exceptions\SarifParsingException;
use App\Services\Sarif\SarifFileInspector;
use Tests\TestCase;

/**
 * Unit tests for the SARIF preflight inspector.
 *
 * These run against real fixture files but use no database: the inspector only
 * opens the file and reads it, so a plain Laravel TestCase is enough.
 */
class SarifFileInspectorTest extends TestCase
{
    private SarifFileInspector $inspector;

    private string $fixturePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inspector = new SarifFileInspector;
        $this->fixturePath = base_path('tests/Fixtures/sarif');
    }

    public function test_it_accepts_a_valid_sarif_file(): void
    {
        $this->inspector->assertSupportedVersion($this->fixturePath.'/eslint-style.sarif');

        $this->expectNotToPerformAssertions();
    }

    public function test_it_rejects_invalid_json(): void
    {
        try {
            $this->inspector->assertSupportedVersion($this->fixturePath.'/malformed.sarif');
            $this->fail('Expected a SarifParsingException for malformed JSON.');
        } catch (SarifParsingException $e) {
            $this->assertSame(ReportFailureReason::InvalidJson, $e->reason);
        }
    }

    public function test_it_rejects_an_unsupported_version(): void
    {
        try {
            $this->inspector->assertSupportedVersion($this->fixturePath.'/unsupported-version.sarif');
            $this->fail('Expected a SarifParsingException for an unsupported version.');
        } catch (SarifParsingException $e) {
            $this->assertSame(ReportFailureReason::UnsupportedVersion, $e->reason);
        }
    }

    public function test_it_rejects_a_document_without_a_version(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'clarif-sarif-');
        file_put_contents($path, '{"runs": []}');

        try {
            $this->inspector->assertSupportedVersion($path);
            $this->fail('Expected a SarifParsingException for a missing version.');
        } catch (SarifParsingException $e) {
            $this->assertSame(ReportFailureReason::UnsupportedVersion, $e->reason);
        } finally {
            @unlink($path);
        }
    }

    public function test_it_rejects_a_native_tool_json_as_not_sarif(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'clarif-sarif-');
        file_put_contents($path, '{"version":"1.176.1","results":[]}');

        try {
            $this->inspector->assertSupportedVersion($path);
            $this->fail('Expected a SarifParsingException for a native tool export.');
        } catch (SarifParsingException $e) {
            $this->assertSame(ReportFailureReason::NotSarif, $e->reason);
        } finally {
            @unlink($path);
        }
    }

    public function test_it_rejects_a_versionless_document_without_runs_as_not_sarif(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'clarif-sarif-');
        file_put_contents($path, '{"@programName":"ZAP","site":[]}');

        try {
            $this->inspector->assertSupportedVersion($path);
            $this->fail('Expected a SarifParsingException for a non-SARIF document.');
        } catch (SarifParsingException $e) {
            $this->assertSame(ReportFailureReason::NotSarif, $e->reason);
        } finally {
            @unlink($path);
        }
    }

    public function test_it_rejects_a_valid_header_with_a_malformed_body(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'clarif-sarif-');
        file_put_contents($path, '{"version":"2.1.0","runs":[]}<?php echo "pwned"; ?>');

        try {
            $this->inspector->assertSupportedVersion($path);
            $this->fail('Expected a SarifParsingException for a malformed body.');
        } catch (SarifParsingException $e) {
            $this->assertSame(ReportFailureReason::InvalidJson, $e->reason);
        } finally {
            @unlink($path);
        }
    }

    public function test_it_rejects_a_non_json_file(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'clarif-sarif-');
        file_put_contents($path, "<?php echo shell_exec('id'); ?>");

        try {
            $this->inspector->assertSupportedVersion($path);
            $this->fail('Expected a SarifParsingException for a non-JSON file.');
        } catch (SarifParsingException $e) {
            $this->assertSame(ReportFailureReason::InvalidJson, $e->reason);
        } finally {
            @unlink($path);
        }
    }
}

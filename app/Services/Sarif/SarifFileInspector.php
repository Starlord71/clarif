<?php

namespace App\Services\Sarif;

use App\Enums\ReportFailureReason;
use App\Exceptions\SarifParsingException;
use JsonMachine\Exception\PathNotFoundException;
use JsonMachine\Exception\SyntaxErrorException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

/**
 * Single source of truth for "what counts as an acceptable SARIF file".
 *
 * This runs as a fast preflight check on the raw upload, before anything is
 * persisted, and is also reused by the parsing job as defense in depth.
 *
 * Two strategies are used depending on file size:
 * - Small files (up to the configured preflight limit) are fully decoded with
 *   the native JSON parser, which guarantees there is no trailing garbage
 *   after the document (e.g. PHP code appended after valid JSON).
 * - Larger files fall back to a streaming header check with json-machine, so a
 *   huge report is never loaded into memory; trailing-garbage detection is
 *   left to the parsing job.
 */
final class SarifFileInspector
{
    /**
     * Assert that the file is valid JSON declaring the supported SARIF version.
     *
     * @throws SarifParsingException When the content is not JSON or the version is not supported.
     */
    public function assertSupportedVersion(string $absolutePath): void
    {
        $size = @filesize($absolutePath);
        $preflightLimit = (int) config('clarif.uploads.preflight_max_kilobytes', 5120) * 1024;

        if (is_int($size) && $preflightLimit > 0 && $size <= $preflightLimit) {
            $this->assertWholeDocumentIsValidJson($absolutePath);

            return;
        }

        $this->assertStreamedVersion($absolutePath);
    }

    /**
     * Fully decode a small file, rejecting malformed or trailing content.
     */
    private function assertWholeDocumentIsValidJson(string $absolutePath): void
    {
        $contents = @file_get_contents($absolutePath);

        if ($contents === false) {
            throw new SarifParsingException(
                ReportFailureReason::InvalidJson,
                'Uploaded file could not be read: '.$absolutePath,
            );
        }

        $decoded = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new SarifParsingException(
                ReportFailureReason::InvalidJson,
                'json_decode failed: '.json_last_error_msg(),
            );
        }

        $version = is_array($decoded) ? ($decoded['version'] ?? null) : null;
        $hasRuns = is_array($decoded) && array_key_exists('runs', $decoded) && is_array($decoded['runs']);

        if (is_string($version) && $version === config('clarif.supported_sarif_version')) {
            return;
        }

        // Valid JSON without a top-level "runs" array is a native tool export
        // (e.g. Semgrep or ZAP JSON), not a SARIF document at all.
        if (! $hasRuns) {
            throw SarifParsingException::notSarif(
                'JSON document has no top-level "runs" array (not SARIF).',
            );
        }

        throw SarifParsingException::unsupportedVersion(
            is_string($version) ? $version : null,
        );
    }

    /**
     * Stream only the top-level version of a large file with json-machine.
     */
    private function assertStreamedVersion(string $absolutePath): void
    {
        try {
            $items = Items::fromFile($absolutePath, [
                'pointer' => '/version',
                'decoder' => new ExtJsonDecoder(true),
            ]);

            $version = null;

            foreach ($items as $key => $value) {
                if ($key === 'version') {
                    $version = $value;
                }
            }

            if (is_string($version) && $version === config('clarif.supported_sarif_version')) {
                return;
            }

            throw SarifParsingException::unsupportedVersion(
                is_string($version) ? $version : null,
            );
        } catch (PathNotFoundException) {
            throw SarifParsingException::unsupportedVersion();
        } catch (SyntaxErrorException $e) {
            throw SarifParsingException::invalidJson($e);
        }
    }
}

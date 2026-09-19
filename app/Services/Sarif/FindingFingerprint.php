<?php

namespace App\Services\Sarif;

/**
 * Builds the grouping fingerprint used to diff findings between two runs.
 *
 * The fingerprint is a pure sha256 over the rule id, the file path and the
 * estimated line. It intentionally ignores the code around the finding, which
 * is the known "line drift problem" documented as a v1 limitation.
 */
final class FindingFingerprint
{
    /**
     * Generate the stable fingerprint for a single finding.
     */
    public function generate(string $ruleId, string $filePath, ?int $line): string
    {
        return hash('sha256', $ruleId.'|'.$filePath.'|'.($line ?? ''));
    }
}

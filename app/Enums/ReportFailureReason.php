<?php

namespace App\Enums;

/**
 * Categorized reasons why a SARIF report failed to be ingested.
 *
 * The label is user-facing and translatable; the enum value is what gets
 * written to the technical log, so logs stay language independent.
 */
enum ReportFailureReason: string
{
    case InvalidJson = 'invalid_json';
    case MissingRuns = 'missing_runs';
    case NotSarif = 'not_sarif';
    case UnsupportedVersion = 'unsupported_version';
    case Unknown = 'unknown';

    /**
     * Get the user-friendly, translatable label for this failure reason.
     */
    public function label(): string
    {
        return __('errors.'.$this->value);
    }
}

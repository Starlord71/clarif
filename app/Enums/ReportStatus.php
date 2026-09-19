<?php

namespace App\Enums;

/**
 * Lifecycle status of an ingested SARIF report.
 */
enum ReportStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    /**
     * Get the user-friendly, translatable label for this status.
     */
    public function label(): string
    {
        return __('reports.statuses.'.$this->value);
    }

    /**
     * Determine whether the report has reached a final state.
     *
     * Terminal reports never change again, so the UI can stop polling them.
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Failed], true);
    }
}

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
}

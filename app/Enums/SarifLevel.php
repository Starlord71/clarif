<?php

namespace App\Enums;

/**
 * Severity levels defined by the SARIF specification.
 *
 * Maps 1:1 to the SARIF "level" vocabulary so it can be cast directly
 * from a normalized result without any translation layer.
 */
enum SarifLevel: string
{
    case Error = 'error';
    case Warning = 'warning';
    case Note = 'note';
    case None = 'none';

    /**
     * Get the user-friendly, translatable label for this severity level.
     */
    public function label(): string
    {
        return __('reports.severities.'.$this->value);
    }
}

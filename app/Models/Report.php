<?php

namespace App\Models;

use App\Enums\ReportFailureReason;
use App\Enums\ReportStatus;
use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'original_filename',
    'tool_name',
    'tool_driver_version',
    'stored_path',
    'status',
    'error_message',
    'meta',
])]
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ReportStatus::class,
            'meta' => 'array',
        ];
    }

    /**
     * Get the findings produced by this report.
     *
     * @return HasMany<Finding, $this>
     */
    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class);
    }

    /**
     * Get the user-facing failure message in the currently active locale.
     *
     * The reason is re-translated on read (instead of trusting the message
     * stored at ingestion time) so a failed report follows the language
     * switcher. It falls back to the persisted message for older records.
     */
    public function failureMessage(): ?string
    {
        $reason = $this->meta['failure_reason'] ?? null;
        $reason = is_string($reason) ? ReportFailureReason::tryFrom($reason) : null;

        return $reason?->label() ?? $this->error_message;
    }
}

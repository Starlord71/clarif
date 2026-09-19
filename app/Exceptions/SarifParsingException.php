<?php

namespace App\Exceptions;

use App\Enums\ReportFailureReason;
use RuntimeException;
use Throwable;

/**
 * Thrown when a SARIF file cannot be processed.
 *
 * The exception carries a categorized reason (used to build the user-facing
 * message) plus a technical message that is only meant for the "sarif" log
 * channel. Raw messages must never reach the end user.
 */
class SarifParsingException extends RuntimeException
{
    public function __construct(
        public readonly ReportFailureReason $reason,
        string $technicalMessage = '',
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            $technicalMessage !== '' ? $technicalMessage : $reason->value,
            0,
            $previous,
        );
    }

    /**
     * Build an exception for a malformed JSON document.
     */
    public static function invalidJson(Throwable $previous): self
    {
        return new self(ReportFailureReason::InvalidJson, $previous->getMessage(), $previous);
    }

    /**
     * Build an exception for a document without a processable runs[0].
     */
    public static function missingRuns(Throwable $previous): self
    {
        return new self(ReportFailureReason::MissingRuns, $previous->getMessage(), $previous);
    }

    /**
     * Build an exception for an unsupported SARIF version.
     */
    public static function unsupportedVersion(?string $version = null): self
    {
        return new self(
            ReportFailureReason::UnsupportedVersion,
            'Unsupported SARIF version: '.($version ?? 'missing'),
        );
    }
}

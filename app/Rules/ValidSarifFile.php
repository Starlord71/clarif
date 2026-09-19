<?php

namespace App\Rules;

use App\Exceptions\SarifParsingException;
use App\Services\Sarif\SarifFileInspector;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Content-based validation rule for uploaded SARIF files.
 *
 * It never trusts the client-provided extension or MIME type: the file is
 * opened and inspected to confirm it is valid JSON with a supported SARIF
 * version. Rejections are logged to the dedicated "sarif" channel as warnings
 * (metadata only, never file contents).
 */
class ValidSarifFile implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        try {
            app(SarifFileInspector::class)->assertSupportedVersion($value->getRealPath());
        } catch (SarifParsingException $e) {
            Log::channel('sarif')->warning('SARIF upload rejected by preflight check.', [
                'filename' => $value->getClientOriginalName(),
                'size' => $value->getSize(),
                'reason' => $e->reason->value,
            ]);

            $fail($e->reason->label());
        }
    }
}

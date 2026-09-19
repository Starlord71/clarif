<?php

namespace App\Services\Sarif;

use App\Enums\SarifLevel;

/**
 * Resolves the severity level of a SARIF result following the precedence
 * documented in the SARIF specification.
 *
 * This service is pure: it only reads the given arrays and never touches the
 * filesystem or the database, which keeps it trivially unit testable.
 */
final class SarifSeverityNormalizer
{
    /**
     * Resolve the severity for a single SARIF result.
     *
     * Precedence:
     * 1. The result's own "level" when present and valid.
     * 2. The referenced rule's "defaultConfiguration.level".
     * 3. The spec fallback: warning.
     *
     * @param  array<string, mixed>  $result  A decoded SARIF result object.
     * @param  array<string, string|null>  $rules  Map of rule id to defaultConfiguration level.
     */
    public function normalize(array $result, array $rules = []): SarifLevel
    {
        $resultLevel = $result['level'] ?? null;

        if (is_string($resultLevel) && ($level = SarifLevel::tryFrom($resultLevel)) !== null) {
            return $level;
        }

        $ruleId = $result['ruleId'] ?? null;

        if (is_string($ruleId)) {
            $ruleLevel = $rules[$ruleId] ?? null;

            if (is_string($ruleLevel) && ($level = SarifLevel::tryFrom($ruleLevel)) !== null) {
                return $level;
            }
        }

        return SarifLevel::Warning;
    }
}

<?php

namespace Tests\Unit\Services;

use App\Enums\SarifLevel;
use App\Services\Sarif\SarifSeverityNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the pure severity normalization precedence rules.
 */
class SarifSeverityNormalizerTest extends TestCase
{
    private SarifSeverityNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new SarifSeverityNormalizer;
    }

    public function test_result_level_wins_when_present_and_valid(): void
    {
        $result = ['ruleId' => 'no-console', 'level' => 'note'];
        $rules = ['no-console' => 'error'];

        $this->assertSame(SarifLevel::Note, $this->normalizer->normalize($result, $rules));
    }

    public function test_rule_level_is_used_when_result_has_no_level(): void
    {
        $result = ['ruleId' => 'no-unused-vars'];
        $rules = ['no-unused-vars' => 'error'];

        $this->assertSame(SarifLevel::Error, $this->normalizer->normalize($result, $rules));
    }

    public function test_falls_back_to_warning_when_no_level_is_available(): void
    {
        $this->assertSame(SarifLevel::Warning, $this->normalizer->normalize(['ruleId' => 'eqeqeq'], []));
        $this->assertSame(SarifLevel::Warning, $this->normalizer->normalize([]));
    }

    public function test_invalid_result_level_falls_through_to_the_rule_level(): void
    {
        $result = ['ruleId' => 'no-console', 'level' => 'fatal'];
        $rules = ['no-console' => 'error'];

        $this->assertSame(SarifLevel::Error, $this->normalizer->normalize($result, $rules));
    }
}

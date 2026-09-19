<?php

namespace Tests\Unit\Enums;

use App\Enums\ReportFailureReason;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

/**
 * Unit tests for the categorized SARIF failure reasons.
 */
class ReportFailureReasonTest extends TestCase
{
    public function test_every_case_has_a_translation_in_english_and_spanish(): void
    {
        foreach (['en', 'es'] as $locale) {
            foreach (ReportFailureReason::cases() as $reason) {
                $key = 'errors.'.$reason->value;

                $this->assertTrue(Lang::has($key, $locale), "Missing translation [$locale]: $key");
                $this->assertNotSame($key, Lang::get($key, [], $locale));
            }
        }
    }

    public function test_the_english_and_spanish_labels_differ(): void
    {
        foreach (ReportFailureReason::cases() as $reason) {
            $this->assertNotSame(
                Lang::get('errors.'.$reason->value, [], 'en'),
                Lang::get('errors.'.$reason->value, [], 'es'),
            );
        }
    }
}

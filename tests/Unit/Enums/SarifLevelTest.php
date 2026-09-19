<?php

namespace Tests\Unit\Enums;

use App\Enums\SarifLevel;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

/**
 * Unit tests for the normalized severity enum.
 */
class SarifLevelTest extends TestCase
{
    public function test_every_case_has_a_translation_in_english_and_spanish(): void
    {
        foreach (['en', 'es'] as $locale) {
            foreach (SarifLevel::cases() as $level) {
                $key = 'reports.severities.'.$level->value;

                $this->assertTrue(Lang::has($key, $locale), "Missing translation [$locale]: $key");
                $this->assertNotSame($key, Lang::get($key, [], $locale));
            }
        }
    }
}

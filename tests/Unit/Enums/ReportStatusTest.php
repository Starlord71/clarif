<?php

namespace Tests\Unit\Enums;

use App\Enums\ReportStatus;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

/**
 * Unit tests for the report lifecycle status enum.
 */
class ReportStatusTest extends TestCase
{
    public function test_every_case_has_a_translation_in_english_and_spanish(): void
    {
        foreach (['en', 'es'] as $locale) {
            foreach (ReportStatus::cases() as $status) {
                $key = 'reports.statuses.'.$status->value;

                $this->assertTrue(Lang::has($key, $locale), "Missing translation [$locale]: $key");
                $this->assertNotSame($key, Lang::get($key, [], $locale));
            }
        }
    }

    public function test_label_follows_the_active_locale(): void
    {
        App::setLocale('en');
        $this->assertSame('Pending', ReportStatus::Pending->label());

        App::setLocale('es');
        $this->assertSame('Pendiente', ReportStatus::Pending->label());
    }

    public function test_is_terminal_only_for_completed_and_failed(): void
    {
        $this->assertTrue(ReportStatus::Completed->isTerminal());
        $this->assertTrue(ReportStatus::Failed->isTerminal());
        $this->assertFalse(ReportStatus::Pending->isTerminal());
        $this->assertFalse(ReportStatus::Processing->isTerminal());
    }
}

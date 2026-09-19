<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

/**
 * Feature tests for the language switcher and the SetLocale middleware.
 *
 * The /about page is used as the target because it renders translated text and
 * does not touch the database.
 */
class LocaleTest extends TestCase
{
    public function test_the_switcher_persists_a_supported_locale_and_redirects_back(): void
    {
        $response = $this->from('/about')->get(route('lang', ['locale' => 'es']));

        $response->assertRedirect('/about');
        $response->assertSessionHas('locale', 'es');
    }

    public function test_the_switcher_rejects_a_locale_outside_the_whitelist(): void
    {
        $this->from('/about')
            ->get(route('lang', ['locale' => 'fr']))
            ->assertNotFound();
    }

    public function test_the_middleware_applies_the_session_locale(): void
    {
        $this->withSession(['locale' => 'es'])
            ->get(route('about'))
            ->assertOk();

        $this->assertSame('es', App::getLocale());
    }

    public function test_the_middleware_falls_back_to_the_default_locale_without_a_session_value(): void
    {
        $this->get(route('about'))->assertOk();

        $this->assertSame(config('app.locale'), App::getLocale());
    }

    public function test_the_middleware_ignores_an_unsupported_session_locale(): void
    {
        $this->withSession(['locale' => 'de'])
            ->get(route('about'))
            ->assertOk();

        $this->assertSame(config('app.locale'), App::getLocale());
    }

    public function test_the_selected_locale_changes_the_rendered_ui(): void
    {
        $this->withSession(['locale' => 'es'])
            ->get(route('about'))
            ->assertOk()
            ->assertSee(Lang::get('about.title', [], 'es'))
            ->assertDontSee(Lang::get('about.title', [], 'en'));
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Feature tests for the standalone help page explaining run comparison.
 */
class HelpPageTest extends TestCase
{
    public function test_the_help_page_renders_the_comparison_explainer(): void
    {
        $response = $this->get(route('help'));

        $response->assertOk();
        $response->assertViewIs('help');
        $response->assertSee(__('help.title'));
        $response->assertSee(__('help.compare_title'));
        $response->assertSee(__('help.compare_new_title'));
        $response->assertSee(__('help.compare_resolved_title'));
        $response->assertSee(__('help.compare_persistent_title'));
        $response->assertSee(__('help.compare_drift_title'));
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Feature tests for the standalone "What is SARIF?" explainer page.
 */
class AboutPageTest extends TestCase
{
    public function test_the_about_page_renders_the_sarif_explainer(): void
    {
        $response = $this->get(route('about'));

        $response->assertOk();
        $response->assertViewIs('about');
        $response->assertSee(__('about.title'));
        $response->assertSee(__('about.tools_title'));
        $response->assertSee(__('about.scope_title'));
        $response->assertSee(__('about.diffing_title'));
    }
}

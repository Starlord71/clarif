<?php

namespace Tests\Feature;

use App\Models\Finding;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Feature tests for the run comparison UI and its HTTP categorization.
 */
class ReportComparisonTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a finding with an explicit fingerprint inside a report.
     */
    private function finding(Report $report, string $ruleId, string $fingerprint, string $message): Finding
    {
        return Finding::factory()->for($report)->create([
            'rule_id' => $ruleId,
            'fingerprint' => $fingerprint,
            'message' => $message,
        ]);
    }

    public function test_the_listing_offers_the_comparison_selector(): void
    {
        Report::factory()->create();

        $response = $this->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('data-compare-form', false);
        $response->assertSee(route('reports.compare'), false);
        $response->assertSee('name="selected[]"', false);
    }

    public function test_it_categorizes_findings_as_new_resolved_and_persistent(): void
    {
        $base = Report::factory()->create(['original_filename' => 'base.sarif']);
        $head = Report::factory()->create(['original_filename' => 'head.sarif']);

        $this->finding($base, 'only-base', 'fp-only-base', 'Base finding message');
        $this->finding($base, 'shared', 'fp-shared', 'Shared base message');

        $this->finding($head, 'only-head', 'fp-only-head', 'Head finding message');
        $this->finding($head, 'shared', 'fp-shared', 'Shared head message');

        $response = $this->get(route('reports.compare', ['base' => $base->id, 'head' => $head->id]));

        $response->assertOk();
        $response->assertViewIs('reports.compare');

        $response->assertViewHas('newFindings', fn (Collection $findings): bool => $findings->pluck('rule_id')->all() === ['only-head']);
        $response->assertViewHas('resolvedFindings', fn (Collection $findings): bool => $findings->pluck('rule_id')->all() === ['only-base']);
        $response->assertViewHas('persistentFindings', fn (Collection $findings): bool => $findings->pluck('rule_id')->all() === ['shared']);

        $response->assertSee(__('reports.compare_help_summary'));
        $response->assertSee(__('reports.compare_help_fingerprint'));

        $response->assertSee('head.sarif');
        $response->assertSee('base.sarif');
        $response->assertSee('Head finding message');
        $response->assertSee('Base finding message');
        $response->assertSee('Shared head message');
    }

    public function test_it_reads_the_comparison_from_older_to_newer_regardless_of_query_order(): void
    {
        $older = Report::factory()->create();
        $newer = Report::factory()->create();

        $response = $this->get(route('reports.compare', ['base' => $newer->id, 'head' => $older->id]));

        $response->assertOk();
        $this->assertTrue($response->viewData('base')->is($older));
        $this->assertTrue($response->viewData('head')->is($newer));
    }

    public function test_it_redirects_when_the_selection_is_invalid(): void
    {
        $report = Report::factory()->create();

        $this->get(route('reports.compare'))
            ->assertRedirect(route('reports.index'))
            ->assertSessionHas('error', __('reports.compare_selection_error'));

        $this->get(route('reports.compare', ['base' => $report->id, 'head' => $report->id]))
            ->assertRedirect(route('reports.index'));

        $this->get(route('reports.compare', ['base' => $report->id, 'head' => 99999]))
            ->assertRedirect(route('reports.index'));
    }
}

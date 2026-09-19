<?php

namespace Tests\Feature;

use App\Enums\ReportStatus;
use App\Enums\SarifLevel;
use App\Models\Finding;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature tests for the interactive report UI: fragments, status polling,
 * pagination defaults and in-place deletion.
 */
class ReportUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_reports_index_defaults_to_ten_rows_per_page(): void
    {
        Report::factory()->count(12)->create();

        $response = $this->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewIs('reports.index');
        $this->assertSame(10, $response->viewData('perPage'));
        $this->assertSame([10, 25, 50], $response->viewData('perPageOptions'));
    }

    public function test_the_reports_index_can_render_just_the_list_fragment(): void
    {
        Report::factory()->create();

        $response = $this->withHeaders(['X-Clarif-Fragment' => 'reports-list'])
            ->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewIs('reports.partials.index-list');
        $response->assertSee('data-fragment="reports-list"', false);
        $response->assertDontSee('<!DOCTYPE html>', false);
    }

    public function test_the_show_page_can_render_just_the_findings_fragment(): void
    {
        $report = Report::factory()->create();
        Finding::factory()->for($report)->create(['message' => 'Unique finding message']);

        $response = $this->withHeaders(['X-Clarif-Fragment' => 'findings-results'])
            ->get(route('reports.show', $report));

        $response->assertOk();
        $response->assertViewIs('reports.partials.findings-results');
        $response->assertSee('data-fragment="findings-results"', false);
        $response->assertSee('Unique finding message');
        $response->assertDontSee('<!DOCTYPE html>', false);
    }

    public function test_the_findings_fragment_applies_the_severity_filter(): void
    {
        $report = Report::factory()->create();

        Finding::factory()->for($report)->create([
            'severity' => SarifLevel::Error,
            'message' => 'Error finding message',
        ]);
        Finding::factory()->for($report)->create([
            'severity' => SarifLevel::Warning,
            'message' => 'Warning finding message',
        ]);

        $response = $this->withHeaders(['X-Clarif-Fragment' => 'findings-results'])
            ->get(route('reports.show', ['report' => $report, 'severity' => 'error']));

        $response->assertOk();
        $response->assertSee('Error finding message');
        $response->assertDontSee('Warning finding message');
    }

    public function test_the_status_endpoint_reports_whether_the_report_is_terminal(): void
    {
        $processing = Report::factory()->create(['status' => ReportStatus::Processing]);
        $completed = Report::factory()->create(['status' => ReportStatus::Completed]);

        $this->getJson(route('reports.status', $processing))
            ->assertOk()
            ->assertJson(['status' => 'processing', 'terminal' => false]);

        $this->getJson(route('reports.status', $completed))
            ->assertOk()
            ->assertJson(['status' => 'completed', 'terminal' => true]);
    }

    public function test_deleting_a_report_through_ajax_returns_a_message_and_removes_it(): void
    {
        Storage::fake('sarif');

        Storage::disk('sarif')->put('report.json', '{}');

        $report = Report::factory()->create(['stored_path' => 'report.json']);
        Finding::factory()->for($report)->create();

        $response = $this->deleteJson(route('reports.destroy', $report));

        $response->assertOk();
        $response->assertJson(['message' => __('reports.report_deleted')]);

        $this->assertDatabaseMissing('reports', ['id' => $report->id]);
        $this->assertDatabaseMissing('findings', ['report_id' => $report->id]);
        Storage::disk('sarif')->assertMissing('report.json');
    }

    public function test_deleting_a_report_without_ajax_still_redirects(): void
    {
        $report = Report::factory()->create();

        $response = $this->delete(route('reports.destroy', $report));

        $response->assertRedirect(route('reports.index'));
        $this->assertDatabaseMissing('reports', ['id' => $report->id]);
    }
}

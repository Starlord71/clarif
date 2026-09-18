<?php

namespace Tests\Feature;

use App\Enums\ReportStatus;
use App\Enums\SarifLevel;
use App\Models\Finding;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration test for the Report and Finding model casts against a real database.
 */
class ReportFindingModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A report with findings exposes the enum and JSONB casts after a DB round-trip.
     */
    public function test_report_with_findings_applies_enum_and_array_casts(): void
    {
        $report = Report::factory()
            ->has(Finding::factory()->count(2), 'findings')
            ->create([
                'status' => ReportStatus::Pending,
                'meta' => ['total_findings' => 2],
            ]);

        $report->refresh();
        $report->load('findings');

        $this->assertInstanceOf(ReportStatus::class, $report->status);
        $this->assertSame(ReportStatus::Pending, $report->status);
        $this->assertIsArray($report->meta);
        $this->assertSame(2, $report->meta['total_findings']);
        $this->assertCount(2, $report->findings);

        $finding = $report->findings->first();

        $this->assertInstanceOf(Finding::class, $finding);
        $this->assertInstanceOf(SarifLevel::class, $finding->severity);
        $this->assertIsArray($finding->payload);
        $this->assertSame($finding->rule_id, $finding->payload['ruleId']);
        $this->assertSame($finding->severity->value, $finding->payload['level']);
    }
}

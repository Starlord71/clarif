<?php

namespace Tests\Feature;

use App\Enums\ReportStatus;
use App\Models\Finding;
use App\Models\Report;
use App\Repositories\EloquentReportRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Direct tests for the Eloquent report repository, the only place that issues
 * queries against the "reports" table.
 */
class EloquentReportRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentReportRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentReportRepository;
    }

    public function test_paginate_latest_orders_newest_first_and_counts_findings(): void
    {
        $older = Report::factory()->create();
        Finding::factory()->count(3)->for($older)->create();

        $newer = Report::factory()->create();
        Finding::factory()->for($newer)->create();

        $page = $this->repository->paginateLatest(10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $page);
        $this->assertSame([$newer->id, $older->id], $page->pluck('id')->all());
        $this->assertSame(1, $page->first()->findings_count);
        $this->assertSame(3, $page->last()->findings_count);
    }

    public function test_paginate_latest_respects_the_per_page_size(): void
    {
        Report::factory()->count(3)->create();

        $page = $this->repository->paginateLatest(2);

        $this->assertCount(2, $page);
        $this->assertSame(3, $page->total());
    }

    public function test_find_returns_the_report_or_null(): void
    {
        $report = Report::factory()->create();

        $this->assertTrue($this->repository->find($report->id)->is($report));
        $this->assertNull($this->repository->find(999999));
    }

    public function test_create_persists_a_report(): void
    {
        $report = $this->repository->create([
            'original_filename' => 'created.sarif',
            'tool_name' => 'ESLint',
            'status' => ReportStatus::Pending,
        ]);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'original_filename' => 'created.sarif',
            'tool_name' => 'ESLint',
        ]);
    }

    public function test_update_mutates_and_returns_the_same_instance(): void
    {
        $report = Report::factory()->create(['tool_name' => 'unknown']);

        $updated = $this->repository->update($report, ['tool_name' => 'CodeQL']);

        $this->assertSame($report, $updated);
        $this->assertSame('CodeQL', $updated->tool_name);
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'tool_name' => 'CodeQL']);
    }

    public function test_delete_removes_the_report(): void
    {
        $report = Report::factory()->create();

        $this->repository->delete($report);

        $this->assertDatabaseMissing('reports', ['id' => $report->id]);
    }

    public function test_count_up_to_counts_reports_inclusively(): void
    {
        $first = Report::factory()->create();
        $second = Report::factory()->create();
        $third = Report::factory()->create();

        $this->assertSame(1, $this->repository->countUpTo($first->id));
        $this->assertSame(2, $this->repository->countUpTo($second->id));
        $this->assertSame(3, $this->repository->countUpTo($third->id));
    }

    public function test_with_findings_eager_loads_the_relation(): void
    {
        $report = Report::factory()->create();
        Finding::factory()->count(2)->for($report)->create();

        $loaded = $this->repository->withFindings($report);

        $this->assertSame($report, $loaded);
        $this->assertTrue($loaded->relationLoaded('findings'));
        $this->assertCount(2, $loaded->findings);
    }
}

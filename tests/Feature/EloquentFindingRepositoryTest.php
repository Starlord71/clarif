<?php

namespace Tests\Feature;

use App\Enums\SarifLevel;
use App\Models\Finding;
use App\Models\Report;
use App\Repositories\EloquentFindingRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Direct tests for the Eloquent finding repository: filtering, batched writes
 * and per-report cleanup.
 */
class EloquentFindingRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentFindingRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentFindingRepository;
    }

    public function test_paginate_for_report_returns_only_its_own_findings(): void
    {
        $report = Report::factory()->create();
        $other = Report::factory()->create();

        Finding::factory()->count(2)->for($report)->create();
        Finding::factory()->for($other)->create();

        $page = $this->repository->paginateForReport($report, [], 10);

        $this->assertSame(2, $page->total());
        $this->assertEmpty(
            $page->pluck('report_id')->reject(fn (int $id): bool => $id === $report->id),
        );
    }

    public function test_paginate_for_report_applies_the_severity_filter(): void
    {
        $report = Report::factory()->create();

        Finding::factory()->for($report)->create(['severity' => SarifLevel::Error]);
        Finding::factory()->for($report)->create(['severity' => SarifLevel::Warning]);

        $page = $this->repository->paginateForReport($report, ['severity' => 'error'], 10);

        $this->assertSame(1, $page->total());
        $this->assertSame(SarifLevel::Error, $page->first()->severity);
    }

    public function test_paginate_for_report_applies_like_filters_on_rule_and_file(): void
    {
        $report = Report::factory()->create();

        Finding::factory()->for($report)->create([
            'rule_id' => 'no-unused-vars',
            'file_path' => 'src/index.js',
        ]);
        Finding::factory()->for($report)->create([
            'rule_id' => 'eqeqeq',
            'file_path' => 'src/utils.js',
        ]);

        $byRule = $this->repository->paginateForReport($report, ['rule_id' => 'unused'], 10);
        $this->assertSame(1, $byRule->total());
        $this->assertSame('no-unused-vars', $byRule->first()->rule_id);

        $byFile = $this->repository->paginateForReport($report, ['file_path' => 'utils'], 10);
        $this->assertSame(1, $byFile->total());
        $this->assertSame('eqeqeq', $byFile->first()->rule_id);
    }

    public function test_insert_batch_persists_every_row(): void
    {
        $report = Report::factory()->create();

        $this->repository->insertBatch([
            $this->row($report, 'rule-a', 'src/a.js', 1, SarifLevel::Error),
            $this->row($report, 'rule-b', 'src/b.js', 2, SarifLevel::Warning),
        ]);

        $this->assertDatabaseCount('findings', 2);
        $this->assertDatabaseHas('findings', ['report_id' => $report->id, 'rule_id' => 'rule-a']);
    }

    public function test_delete_for_report_removes_only_its_findings(): void
    {
        $report = Report::factory()->create();
        $other = Report::factory()->create();

        Finding::factory()->count(2)->for($report)->create();
        Finding::factory()->for($other)->create();

        $this->repository->deleteForReport($report);

        $this->assertDatabaseCount('findings', 1);
        $this->assertDatabaseHas('findings', ['report_id' => $other->id]);
    }

    /**
     * Build a raw findings row exactly as the ingestion service would.
     *
     * @return array<string, mixed>
     */
    private function row(Report $report, string $ruleId, string $filePath, int $line, SarifLevel $severity): array
    {
        $now = now();

        return [
            'report_id' => $report->id,
            'rule_id' => $ruleId,
            'file_path' => $filePath,
            'line' => $line,
            'severity' => $severity->value,
            'message' => 'Message for '.$ruleId,
            'fingerprint' => hash('sha256', $ruleId.'|'.$filePath.'|'.$line),
            'payload' => json_encode(['ruleId' => $ruleId]),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}

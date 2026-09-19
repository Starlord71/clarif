<?php

namespace App\Repositories;

use App\Contracts\FindingRepositoryInterface;
use App\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent/query-builder backed implementation of the finding persistence contract.
 *
 * Findings are the only high-volume table, so the batch insert lives here.
 * Every write is wrapped in a transaction to keep a partial chunk from being
 * persisted when a batch fails.
 */
final class EloquentFindingRepository implements FindingRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function paginateForReport(Report $report, array $filters, int $perPage): LengthAwarePaginator
    {
        $severity = $filters['severity'] ?? '';
        $ruleId = $filters['rule_id'] ?? '';
        $filePath = $filters['file_path'] ?? '';

        return $report->findings()
            ->when($severity !== '', fn ($query) => $query->where('severity', $severity))
            ->when($ruleId !== '', fn ($query) => $query->where('rule_id', 'like', '%'.$ruleId.'%'))
            ->when($filePath !== '', fn ($query) => $query->where('file_path', 'like', '%'.$filePath.'%'))
            ->orderBy('id')
            ->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function insertBatch(array $rows): void
    {
        DB::transaction(function () use ($rows): void {
            DB::table('findings')->insert($rows);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function deleteForReport(Report $report): void
    {
        DB::table('findings')->where('report_id', $report->id)->delete();
    }
}

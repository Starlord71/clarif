<?php

namespace App\Repositories;

use App\Contracts\ReportRepositoryInterface;
use App\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Eloquent-backed implementation of the report persistence contract.
 *
 * This class owns every query against the "reports" table. Callers get plain
 * Eloquent models back, so the rest of the application stays unaware of how
 * the data is stored.
 */
final class EloquentReportRepository implements ReportRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function paginateLatest(int $perPage): LengthAwarePaginator
    {
        return Report::query()
            ->withCount('findings')
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $id): ?Report
    {
        return Report::query()->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $attributes): Report
    {
        return Report::create($attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function update(Report $report, array $attributes): Report
    {
        $report->update($attributes);

        return $report;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Report $report): void
    {
        $report->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function countUpTo(int $id): int
    {
        return Report::query()->where('id', '<=', $id)->count();
    }

    /**
     * {@inheritDoc}
     */
    public function withFindings(Report $report): Report
    {
        return $report->load('findings');
    }
}

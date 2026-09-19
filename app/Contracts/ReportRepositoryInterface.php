<?php

namespace App\Contracts;

use App\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Persistence contract for SARIF reports.
 *
 * Controllers and services depend on this abstraction so they never issue
 * queries themselves. The implementation decides how reports are read and
 * written, which keeps the HTTP layer free of data-access concerns.
 */
interface ReportRepositoryInterface
{
    /**
     * Paginate every report, newest first, with its findings count eager loaded.
     */
    public function paginateLatest(int $perPage): LengthAwarePaginator;

    /**
     * Find a report by its primary key, or null when it does not exist.
     */
    public function find(int $id): ?Report;

    /**
     * Persist a new report and return the created model.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Report;

    /**
     * Update the given report.
     *
     * Implementations mutate the given model in place and return that same
     * instance. Callers should use the returned value instead of assuming the
     * argument was mutated.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Report $report, array $attributes): Report;

    /**
     * Delete the given report.
     */
    public function delete(Report $report): void;

    /**
     * Count how many reports exist up to and including the given id.
     *
     * Used to expose the stable, 1-based creation order of a report.
     */
    public function countUpTo(int $id): int;

    /**
     * Load the findings relation on the given report.
     *
     * Implementations eager load the relation on the given model and return
     * that same instance. Callers should use the returned value instead of
     * assuming the argument was mutated.
     */
    public function withFindings(Report $report): Report;
}

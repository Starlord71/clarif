<?php

namespace App\Contracts;

use App\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Persistence contract for report findings.
 *
 * This is the only place allowed to touch the findings table with the query
 * builder. Keeping the batch insert here lets the ingestion service stay free
 * of SQL details and become trivial to test with an in-memory fake.
 */
interface FindingRepositoryInterface
{
    /**
     * Paginate the findings of a report, applying the given filters.
     *
     * Supported filter keys: "severity", "rule_id" and "file_path". Empty
     * values are ignored.
     *
     * @param  array<string, string>  $filters
     */
    public function paginateForReport(Report $report, array $filters, int $perPage): LengthAwarePaginator;

    /**
     * Insert a batch of finding rows inside a single transaction.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function insertBatch(array $rows): void;

    /**
     * Remove every finding belonging to the given report.
     */
    public function deleteForReport(Report $report): void;
}

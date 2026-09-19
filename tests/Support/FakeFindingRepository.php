<?php

namespace Tests\Support;

use App\Contracts\FindingRepositoryInterface;
use App\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use RuntimeException;

/**
 * In-memory finding repository used to unit test services without a database.
 *
 * Every batch passed to insertBatch is recorded so tests can assert on the
 * exact rows the service would have written.
 */
final class FakeFindingRepository implements FindingRepositoryInterface
{
    /**
     * Batches received through insertBatch, in call order.
     *
     * @var array<int, array<int, array<string, mixed>>>
     */
    public array $batches = [];

    /**
     * Number of deleteForReport calls received.
     */
    public int $deleteCalls = 0;

    /**
     * {@inheritDoc}
     */
    public function paginateForReport(Report $report, array $filters, int $perPage): LengthAwarePaginator
    {
        throw new RuntimeException('Not used in this test.');
    }

    /**
     * {@inheritDoc}
     */
    public function insertBatch(array $rows): void
    {
        $this->batches[] = $rows;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteForReport(Report $report): void
    {
        $this->deleteCalls++;
    }

    /**
     * Flatten every inserted row across all batches.
     *
     * @return array<int, array<string, mixed>>
     */
    public function allRows(): array
    {
        return array_merge(...$this->batches ?: [[]]);
    }
}

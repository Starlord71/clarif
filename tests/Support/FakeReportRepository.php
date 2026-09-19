<?php

namespace Tests\Support;

use App\Contracts\ReportRepositoryInterface;
use App\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use RuntimeException;

/**
 * In-memory report repository used to unit test services without a database.
 *
 * The update method mirrors Eloquent by mutating the given model, so tests can
 * still read back the resulting attributes (status, meta, tool name).
 */
final class FakeReportRepository implements ReportRepositoryInterface
{
    /**
     * Number of update calls received.
     */
    public int $updateCalls = 0;

    /**
     * {@inheritDoc}
     */
    public function paginateLatest(int $perPage): LengthAwarePaginator
    {
        throw new RuntimeException('Not used in this test.');
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $id): ?Report
    {
        throw new RuntimeException('Not used in this test.');
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $attributes): Report
    {
        return new Report($attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function update(Report $report, array $attributes): Report
    {
        $this->updateCalls++;

        foreach ($attributes as $key => $value) {
            $report->setAttribute($key, $value);
        }

        return $report;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Report $report): void
    {
        throw new RuntimeException('Not used in this test.');
    }

    /**
     * {@inheritDoc}
     */
    public function countUpTo(int $id): int
    {
        throw new RuntimeException('Not used in this test.');
    }

    /**
     * {@inheritDoc}
     */
    public function withFindings(Report $report): Report
    {
        return $report;
    }
}

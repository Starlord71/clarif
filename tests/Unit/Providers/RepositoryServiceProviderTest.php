<?php

namespace Tests\Unit\Providers;

use App\Contracts\FindingRepositoryInterface;
use App\Contracts\ReportRepositoryInterface;
use App\Repositories\EloquentFindingRepository;
use App\Repositories\EloquentReportRepository;
use Tests\TestCase;

/**
 * Tests the persistence bindings declared by the RepositoryServiceProvider.
 */
class RepositoryServiceProviderTest extends TestCase
{
    public function test_the_report_contract_resolves_to_the_eloquent_implementation(): void
    {
        $this->assertInstanceOf(
            EloquentReportRepository::class,
            $this->app->make(ReportRepositoryInterface::class),
        );
    }

    public function test_the_finding_contract_resolves_to_the_eloquent_implementation(): void
    {
        $this->assertInstanceOf(
            EloquentFindingRepository::class,
            $this->app->make(FindingRepositoryInterface::class),
        );
    }

    public function test_the_bindings_are_shared_singletons(): void
    {
        $this->assertSame(
            $this->app->make(ReportRepositoryInterface::class),
            $this->app->make(ReportRepositoryInterface::class),
        );

        $this->assertSame(
            $this->app->make(FindingRepositoryInterface::class),
            $this->app->make(FindingRepositoryInterface::class),
        );
    }
}

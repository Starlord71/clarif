<?php

namespace App\Providers;

use App\Contracts\FindingRepositoryInterface;
use App\Contracts\ReportRepositoryInterface;
use App\Repositories\EloquentFindingRepository;
use App\Repositories\EloquentReportRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds the persistence contracts to their Eloquent implementations.
 *
 * Dependencies are resolved by interface everywhere in the application, so a
 * different storage strategy can be swapped in by changing this mapping alone.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the repository bindings.
     */
    public function register(): void
    {
        $this->app->singleton(ReportRepositoryInterface::class, EloquentReportRepository::class);
        $this->app->singleton(FindingRepositoryInterface::class, EloquentFindingRepository::class);
    }
}

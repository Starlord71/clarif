<?php

namespace App\Jobs;

use App\Models\Report;
use App\Services\Sarif\SarifIngestionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Queues the parsing of an uploaded SARIF file.
 *
 * This job is intentionally thin: it only adapts the queue to the ingestion
 * service, which holds the parsing logic and persists through the repository
 * contracts. That keeps all business rules out of the queue infrastructure.
 */
class ParseSarifReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  string  $storedPath  Path of the uploaded file relative to the configured disk.
     */
    public function __construct(
        public readonly Report $report,
        public readonly string $storedPath,
    ) {}

    /**
     * Execute the job through the ingestion service.
     */
    public function handle(SarifIngestionService $ingestion): void
    {
        $ingestion->ingest($this->report, $this->storedPath);
    }
}

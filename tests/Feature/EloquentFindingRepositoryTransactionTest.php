<?php

namespace Tests\Feature;

use App\Enums\SarifLevel;
use App\Models\Report;
use App\Repositories\EloquentFindingRepository;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Verifies that the batch insert is wrapped in a database transaction.
 *
 * This class uses DatabaseMigrations (instead of RefreshDatabase) so there is
 * no outer test transaction hiding the connection-level transaction events
 * fired by the repository.
 */
class EloquentFindingRepositoryTransactionTest extends TestCase
{
    use DatabaseMigrations;

    public function test_insert_batch_opens_and_commits_a_transaction(): void
    {
        $report = Report::factory()->create();

        $events = Event::fake([TransactionBeginning::class, TransactionCommitted::class]);
        DB::connection()->setEventDispatcher($events);

        (new EloquentFindingRepository)->insertBatch([
            [
                'report_id' => $report->id,
                'rule_id' => 'rule-a',
                'file_path' => 'src/a.js',
                'line' => 1,
                'severity' => SarifLevel::Error->value,
                'message' => 'Message',
                'fingerprint' => hash('sha256', 'rule-a|src/a.js|1'),
                'payload' => json_encode(['ruleId' => 'rule-a']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Event::assertDispatched(TransactionBeginning::class);
        Event::assertDispatched(TransactionCommitted::class);
        $this->assertDatabaseCount('findings', 1);
    }
}

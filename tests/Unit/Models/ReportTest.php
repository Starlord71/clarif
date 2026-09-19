<?php

namespace Tests\Unit\Models;

use App\Enums\ReportFailureReason;
use App\Models\Report;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

/**
 * Unit tests for the report model's user-facing failure message.
 *
 * The message is re-translated on read so a failed report follows the active
 * locale, with a fallback to the raw stored message for legacy records.
 */
class ReportTest extends TestCase
{
    public function test_failure_message_is_retranslated_from_the_stored_reason(): void
    {
        $report = new Report([
            'error_message' => 'stale persisted message',
            'meta' => ['failure_reason' => ReportFailureReason::NotSarif->value],
        ]);

        App::setLocale('en');
        $this->assertSame(ReportFailureReason::NotSarif->label(), $report->failureMessage());

        App::setLocale('es');
        $this->assertSame(ReportFailureReason::NotSarif->label(), $report->failureMessage());
        $this->assertNotSame('stale persisted message', $report->failureMessage());
    }

    public function test_failure_message_falls_back_to_the_persisted_message_without_a_reason(): void
    {
        $report = new Report(['error_message' => 'Legacy raw message']);

        $this->assertSame('Legacy raw message', $report->failureMessage());
    }

    public function test_failure_message_ignores_an_unknown_reason_and_falls_back(): void
    {
        $report = new Report([
            'error_message' => 'Legacy raw message',
            'meta' => ['failure_reason' => 'not-a-real-reason'],
        ]);

        $this->assertSame('Legacy raw message', $report->failureMessage());
    }

    public function test_failure_message_is_null_without_reason_and_message(): void
    {
        $this->assertNull((new Report)->failureMessage());
    }
}

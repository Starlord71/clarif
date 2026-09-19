<?php

namespace Tests\Unit\Services;

use App\Models\Finding;
use App\Models\Report;
use App\Services\Sarif\ReportComparisonService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the pure, fingerprint-based comparison service.
 *
 * No database is involved: reports are plain models whose "findings"
 * relation is populated in memory.
 */
class ReportComparisonServiceTest extends TestCase
{
    private ReportComparisonService $comparison;

    protected function setUp(): void
    {
        parent::setUp();

        $this->comparison = new ReportComparisonService;
    }

    /**
     * Build an unsaved report with its findings relation already loaded.
     *
     * @param  array<int, string>  $fingerprints
     */
    private function reportWithFingerprints(array $fingerprints): Report
    {
        $findings = new Collection(array_map(
            fn (string $fingerprint): Finding => new Finding(['fingerprint' => $fingerprint]),
            $fingerprints,
        ));

        $report = new Report;
        $report->setRelation('findings', $findings);

        return $report;
    }

    public function test_it_classifies_findings_only_in_head_as_new(): void
    {
        $result = $this->comparison->compare(
            $this->reportWithFingerprints(['a', 'b']),
            $this->reportWithFingerprints(['b', 'c']),
        );

        $this->assertSame(['c'], $result['new']->pluck('fingerprint')->all());
    }

    public function test_it_classifies_findings_only_in_base_as_resolved(): void
    {
        $result = $this->comparison->compare(
            $this->reportWithFingerprints(['a', 'b']),
            $this->reportWithFingerprints(['b', 'c']),
        );

        $this->assertSame(['a'], $result['resolved']->pluck('fingerprint')->all());
    }

    public function test_it_classifies_findings_in_both_runs_as_persistent(): void
    {
        $result = $this->comparison->compare(
            $this->reportWithFingerprints(['a', 'b']),
            $this->reportWithFingerprints(['b', 'c']),
        );

        $this->assertSame(['b'], $result['persistent']->pluck('fingerprint')->all());
    }

    public function test_it_returns_the_head_version_of_a_persistent_finding(): void
    {
        $baseFinding = new Finding(['fingerprint' => 'a', 'message' => 'old message']);
        $headFinding = new Finding(['fingerprint' => 'a', 'message' => 'new message']);

        $base = new Report;
        $base->setRelation('findings', new Collection([$baseFinding]));

        $head = new Report;
        $head->setRelation('findings', new Collection([$headFinding]));

        $result = $this->comparison->compare($base, $head);

        $this->assertSame($headFinding, $result['persistent']->first());
    }

    public function test_it_handles_completely_disjoint_runs(): void
    {
        $result = $this->comparison->compare(
            $this->reportWithFingerprints(['a']),
            $this->reportWithFingerprints(['b']),
        );

        $this->assertSame(['b'], $result['new']->pluck('fingerprint')->all());
        $this->assertSame(['a'], $result['resolved']->pluck('fingerprint')->all());
        $this->assertTrue($result['persistent']->isEmpty());
    }

    public function test_it_handles_reports_without_findings(): void
    {
        $result = $this->comparison->compare(
            $this->reportWithFingerprints([]),
            $this->reportWithFingerprints([]),
        );

        $this->assertTrue($result['new']->isEmpty());
        $this->assertTrue($result['resolved']->isEmpty());
        $this->assertTrue($result['persistent']->isEmpty());
    }
}

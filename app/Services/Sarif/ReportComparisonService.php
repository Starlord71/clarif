<?php

namespace App\Services\Sarif;

use App\Models\Finding;
use App\Models\Report;
use Illuminate\Support\Collection;

/**
 * Diffs two parsed runs by classifying every finding using its fingerprint.
 *
 * The service is intentionally pure: it only works on the "findings"
 * relations already loaded in memory, so it never issues a query of its own.
 * Callers are responsible for eager loading both relations before comparing.
 */
final class ReportComparisonService
{
    /**
     * Compare an older run (base) against a newer one (head).
     *
     * A finding is classified by fingerprint identity:
     * - new: present in head, absent from base.
     * - resolved: present in base, absent from head.
     * - persistent: present in both runs (head's version is returned).
     *
     * @return array{new: Collection<int, Finding>, resolved: Collection<int, Finding>, persistent: Collection<int, Finding>}
     */
    public function compare(Report $base, Report $head): array
    {
        $baseFindings = $base->findings;
        $headFindings = $head->findings;

        $baseFingerprints = $baseFindings->pluck('fingerprint')->flip();
        $headFingerprints = $headFindings->pluck('fingerprint')->flip();

        return [
            'new' => $headFindings
                ->reject(fn (Finding $finding): bool => $baseFingerprints->has($finding->fingerprint))
                ->values(),
            'resolved' => $baseFindings
                ->reject(fn (Finding $finding): bool => $headFingerprints->has($finding->fingerprint))
                ->values(),
            'persistent' => $headFindings
                ->filter(fn (Finding $finding): bool => $baseFingerprints->has($finding->fingerprint))
                ->values(),
        ];
    }
}

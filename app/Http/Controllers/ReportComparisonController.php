<?php

namespace App\Http\Controllers;

use App\Contracts\ReportRepositoryInterface;
use App\Models\Report;
use App\Services\Sarif\ReportComparisonService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Renders the diff between two parsed runs as new, resolved and persistent.
 */
class ReportComparisonController extends Controller
{
    /**
     * Inject the report repository and the pure comparison service.
     */
    public function __construct(
        private readonly ReportRepositoryInterface $reports,
        private readonly ReportComparisonService $comparison,
    ) {}

    /**
     * Compare the two reports identified by the base and head query params.
     */
    public function __invoke(Request $request): View|RedirectResponse
    {
        $base = $this->reports->find($request->integer('base'));
        $head = $this->reports->find($request->integer('head'));

        if ($base === null || $head === null || $base->is($head)) {
            return redirect()
                ->route('reports.index')
                ->with('error', __('reports.compare_selection_error'));
        }

        // Always read the comparison as older -> newer, whatever the order
        // the two reports were selected in.
        if ($base->id > $head->id) {
            [$base, $head] = [$head, $base];
        }

        $base = $this->reports->withFindings($base);
        $head = $this->reports->withFindings($head);

        $comparison = $this->comparison->compare($base, $head);

        return view('reports.compare', [
            'base' => $base,
            'head' => $head,
            'baseNumber' => $this->reportNumber($base),
            'headNumber' => $this->reportNumber($head),
            'newFindings' => $comparison['new'],
            'resolvedFindings' => $comparison['resolved'],
            'persistentFindings' => $comparison['persistent'],
        ]);
    }

    /**
     * Get the stable, 1-based position of a report in creation order.
     */
    private function reportNumber(Report $report): int
    {
        return $this->reports->countUpTo($report->id);
    }
}

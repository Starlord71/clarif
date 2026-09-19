<?php

namespace App\Http\Controllers;

use App\Contracts\FindingRepositoryInterface;
use App\Contracts\ReportRepositoryInterface;
use App\Enums\SarifLevel;
use App\Models\Report;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Serves the read-only report UI: listing and detail with finding filters.
 *
 * The same endpoints render either the full page or a bare HTML fragment
 * (identified by the X-Clarif-Fragment header), so the front end can refresh
 * just the affected region through fetch without a full reload.
 */
class ReportController extends Controller
{
    /**
     * Inject the persistence contracts used by this controller.
     */
    public function __construct(
        private readonly ReportRepositoryInterface $reports,
        private readonly FindingRepositoryInterface $findingsRepository,
    ) {}

    /**
     * Rows per page offered by the pagination selector.
     */
    private const PER_PAGE_OPTIONS = [10, 25, 50];

    /**
     * Fragment name for the report listing region.
     */
    private const FRAGMENT_LIST = 'reports-list';

    /**
     * Fragment name for the report status and detail region.
     */
    private const FRAGMENT_STATUS = 'report-status';

    /**
     * Fragment name for the paginated findings region.
     */
    private const FRAGMENT_FINDINGS = 'findings-results';

    /**
     * List every ingested report with its status and findings count.
     */
    public function index(Request $request): View
    {
        $perPage = $this->resolvePerPage($request, 10);

        $reports = $this->reports
            ->paginateLatest($perPage)
            ->withQueryString();

        $data = [
            'reports' => $reports,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'hasPending' => $reports->contains(
                fn (Report $report): bool => ! $report->status->isTerminal(),
            ),
        ];

        if ($this->wantsFragment($request, self::FRAGMENT_LIST)) {
            return view('reports.partials.index-list', $data);
        }

        return view('reports.index', $data);
    }

    /**
     * Show a single report and its paginated, filterable findings.
     */
    public function show(Request $request, Report $report): View
    {
        $perPage = $this->resolvePerPage($request, 25);

        $severity = (string) $request->query('severity', '');

        if (SarifLevel::tryFrom($severity) === null) {
            $severity = '';
        }

        $ruleId = trim((string) $request->query('rule_id', ''));
        $filePath = trim((string) $request->query('file_path', ''));

        $findings = $this->findingsRepository
            ->paginateForReport($report, [
                'severity' => $severity,
                'rule_id' => $ruleId,
                'file_path' => $filePath,
            ], $perPage)
            ->withQueryString();

        $data = [
            'report' => $report,
            'findings' => $findings,
            'severity' => $severity,
            'ruleId' => $ruleId,
            'filePath' => $filePath,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'reportNumber' => $this->reportNumber($report),
            'totalFindings' => $report->meta['total_findings'] ?? null,
            'hasFilters' => $severity !== '' || $ruleId !== '' || $filePath !== '',
        ];

        if ($this->wantsFragment($request, self::FRAGMENT_STATUS)) {
            return view('reports.partials.show-status', $data);
        }

        if ($this->wantsFragment($request, self::FRAGMENT_FINDINGS)) {
            return view('reports.partials.findings-results', $data);
        }

        return view('reports.show', $data);
    }

    /**
     * Report the current lifecycle status of a report as JSON.
     *
     * Used by the detail page to poll the parsing job without reloading.
     */
    public function status(Report $report): JsonResponse
    {
        return response()->json([
            'status' => $report->status->value,
            'terminal' => $report->status->isTerminal(),
        ]);
    }

    /**
     * Delete a report, its findings (cascade) and its stored SARIF file.
     *
     * AJAX callers receive a JSON success message so the row can be removed
     * in place; regular submissions keep the redirect to the listing.
     */
    public function destroy(Request $request, Report $report): RedirectResponse|JsonResponse
    {
        if (is_string($report->stored_path) && $report->stored_path !== '') {
            Storage::disk(config('clarif.uploads.disk'))->delete($report->stored_path);
        }

        $this->reports->delete($report);

        if ($request->expectsJson()) {
            return response()->json(['message' => __('reports.report_deleted')]);
        }

        return redirect()
            ->route('reports.index')
            ->with('status', __('reports.report_deleted'));
    }

    /**
     * Resolve the requested page size, falling back to the default.
     */
    private function resolvePerPage(Request $request, int $default): int
    {
        $perPage = (int) $request->query('per_page', $default);

        return in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : $default;
    }

    /**
     * Determine whether the request asked for a single HTML fragment.
     */
    private function wantsFragment(Request $request, string $fragment): bool
    {
        return $request->header('X-Clarif-Fragment') === $fragment;
    }

    /**
     * Get the stable, 1-based position of a report in creation order.
     */
    private function reportNumber(Report $report): int
    {
        return $this->reports->countUpTo($report->id);
    }
}

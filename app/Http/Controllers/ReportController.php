<?php

namespace App\Http\Controllers;

use App\Enums\SarifLevel;
use App\Models\Report;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Serves the read-only report UI: listing and detail with finding filters.
 */
class ReportController extends Controller
{
    /**
     * Rows per page offered by the pagination selector.
     */
    private const PER_PAGE_OPTIONS = [15, 25, 50];

    /**
     * List every ingested report with its status and findings count.
     */
    public function index(Request $request): View
    {
        $perPage = $this->resolvePerPage($request, 15);

        $reports = Report::query()
            ->withCount('findings')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('reports.index', [
            'reports' => $reports,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
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

        $findings = $report->findings()
            ->when($severity !== '', fn ($query) => $query->where('severity', $severity))
            ->when($ruleId !== '', fn ($query) => $query->where('rule_id', 'like', '%'.$ruleId.'%'))
            ->when($filePath !== '', fn ($query) => $query->where('file_path', 'like', '%'.$filePath.'%'))
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('reports.show', [
            'report' => $report,
            'findings' => $findings,
            'severity' => $severity,
            'ruleId' => $ruleId,
            'filePath' => $filePath,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'reportNumber' => $this->reportNumber($report),
        ]);
    }

    /**
     * Delete a report, its findings (cascade) and its stored SARIF file.
     */
    public function destroy(Report $report): RedirectResponse
    {
        if (is_string($report->stored_path) && $report->stored_path !== '') {
            Storage::disk(config('clarif.uploads.disk'))->delete($report->stored_path);
        }

        $report->delete();

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
     * Get the stable, 1-based position of a report in creation order.
     */
    private function reportNumber(Report $report): int
    {
        return Report::query()->where('id', '<=', $report->id)->count();
    }
}

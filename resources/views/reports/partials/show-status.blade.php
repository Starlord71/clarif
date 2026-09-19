<div id="report-status" data-fragment="report-status"
     data-terminal="{{ $report->status->isTerminal() ? '1' : '0' }}"
     data-status-url="{{ route('reports.status', $report) }}"
     class="mb-8">
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-semibold text-slate-900">
            {{ __('reports.report_title', ['id' => $reportNumber]) }}
        </h1>
        <x-status-badge :status="$report->status" />
    </div>

    @if ($report->status === \App\Enums\ReportStatus::Failed)
        <div class="mt-4 rounded-md border border-red-200 bg-red-50 p-4">
            <p class="font-medium text-red-800">{{ __('reports.failure_title') }}</p>
            <p class="mt-1 text-sm text-red-700">{{ $report->failureMessage() }}</p>
            <p class="mt-2 text-xs font-medium text-red-500">
                {{ __('reports.failure_reference', ['id' => $report->id]) }}
            </p>
        </div>
    @endif

    <dl class="mt-6 grid grid-cols-1 gap-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('reports.detail_filename') }}</dt>
            <dd class="mt-1 break-all text-sm font-medium text-slate-800">{{ $report->original_filename }}</dd>
        </div>
        <div>
            <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('reports.detail_tool') }}</dt>
            <dd class="mt-1 text-sm font-medium text-slate-800">{{ $report->tool_name }}</dd>
        </div>
        <div>
            <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('reports.detail_version') }}</dt>
            <dd class="mt-1 text-sm font-medium text-slate-800">
                {{ $report->tool_driver_version ?: __('reports.detail_unknown') }}
            </dd>
        </div>
        <div>
            <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('reports.detail_total_findings') }}</dt>
            <dd class="mt-1 text-sm font-medium text-slate-800">{{ $totalFindings ?? __('reports.value_unknown') }}</dd>
        </div>
        <div>
            <dt class="text-xs uppercase tracking-wide text-slate-500">{{ __('reports.detail_uploaded') }}</dt>
            <dd class="mt-1 text-sm font-medium text-slate-800">{{ $report->created_at->format('Y-m-d H:i') }}</dd>
        </div>
    </dl>
</div>

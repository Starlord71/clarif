@php
    $roleClasses = $accent === 'base'
        ? 'bg-slate-100 text-slate-600 ring-slate-200'
        : 'bg-indigo-50 text-indigo-700 ring-indigo-200';
@endphp

<div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $roleClasses }}">
            {{ $role }}
        </span>
        <span class="font-mono text-xs text-slate-400">
            {{ __('reports.report_title', ['id' => $reportNumber]) }}
        </span>
    </div>

    <p class="mt-3 break-all font-medium text-slate-800" title="{{ $report->original_filename }}">
        {{ $report->original_filename }}
    </p>

    <p class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500">
        <span>{{ __('reports.detail_tool') }}: <span class="font-medium text-slate-600">{{ $report->tool_name }}</span></span>
        <span>{{ __('reports.detail_uploaded') }}: <span class="font-medium text-slate-600">{{ $report->created_at->format('Y-m-d H:i') }}</span></span>
        <span>{{ __('reports.column_findings') }}: <span class="font-medium text-slate-600">{{ $report->findings->count() }}</span></span>
    </p>
</div>

@extends('layouts.app')

@section('title', __('reports.report_title', ['id' => $reportNumber]))

@section('content')
    @php
        $totalFindings = $report->meta['total_findings'] ?? null;
        $hasFilters = $severity !== '' || $ruleId !== '' || $filePath !== '';
    @endphp

    <div class="mb-6">
        <a href="{{ route('reports.index') }}"
           class="text-sm font-medium text-slate-500 transition hover:text-slate-700">
            &larr; {{ __('reports.back_to_reports') }}
        </a>
        <div class="mt-2 flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-semibold text-slate-900">
                {{ __('reports.report_title', ['id' => $reportNumber]) }}
            </h1>
            <x-status-badge :status="$report->status" />
        </div>
    </div>

    @if ($report->status === \App\Enums\ReportStatus::Failed)
        <div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4">
            <p class="font-medium text-red-800">{{ __('reports.failure_title') }}</p>
            <p class="mt-1 text-sm text-red-700">{{ $report->failureMessage() }}</p>
            <p class="mt-2 text-xs font-medium text-red-500">
                {{ __('reports.failure_reference', ['id' => $report->id]) }}
            </p>
        </div>
    @endif

    <dl class="mb-8 grid grid-cols-1 gap-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm sm:grid-cols-2 lg:grid-cols-3">
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

    <form method="GET" action="{{ route('reports.show', $report) }}"
          class="mb-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <p class="mb-3 text-sm font-medium text-slate-700">{{ __('reports.filters_title') }}</p>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="severity" class="block text-xs font-medium text-slate-500">
                    {{ __('reports.filter_severity') }}
                </label>
                <select name="severity" id="severity"
                        class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                    <option value="">{{ __('reports.filter_all') }}</option>
                    @foreach (\App\Enums\SarifLevel::cases() as $level)
                        <option value="{{ $level->value }}" @selected($severity === $level->value)>
                            {{ $level->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="rule_id" class="block text-xs font-medium text-slate-500">
                    {{ __('reports.filter_rule') }}
                </label>
                <input type="text" name="rule_id" id="rule_id" value="{{ $ruleId }}"
                       placeholder="{{ __('reports.filter_rule_placeholder') }}"
                       class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700" />
            </div>
            <div>
                <label for="file_path" class="block text-xs font-medium text-slate-500">
                    {{ __('reports.filter_file') }}
                </label>
                <input type="text" name="file_path" id="file_path" value="{{ $filePath }}"
                       placeholder="{{ __('reports.filter_file_placeholder') }}"
                       class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700" />
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                    {{ __('reports.filter_apply') }}
                </button>
                @if ($hasFilters)
                    <a href="{{ route('reports.show', $report) }}"
                       class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100">
                        {{ __('reports.filter_reset') }}
                    </a>
                @endif
            </div>
        </div>
    </form>

    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('reports.findings_title') }}</h2>

        <form method="GET" action="{{ route('reports.show', $report) }}" class="flex items-center gap-2">
            @if ($severity !== '')
                <input type="hidden" name="severity" value="{{ $severity }}">
            @endif
            @if ($ruleId !== '')
                <input type="hidden" name="rule_id" value="{{ $ruleId }}">
            @endif
            @if ($filePath !== '')
                <input type="hidden" name="file_path" value="{{ $filePath }}">
            @endif
            <label for="per_page" class="text-sm text-slate-500">{{ __('reports.per_page_label') }}</label>
            <select name="per_page" id="per_page" onchange="this.form.submit()"
                    class="rounded-md border border-slate-300 bg-white px-2 py-1 text-sm text-slate-700">
                @foreach ($perPageOptions as $option)
                    <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if ($findings->isEmpty())
        <div class="rounded-lg border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-500">
            {{ $hasFilters ? __('reports.findings_no_match') : __('reports.findings_empty') }}
        </div>
    @else
        <div class="overflow-x-auto rounded-lg border border-slate-200 shadow-sm">
            <table class="clarif-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3">{{ __('reports.column_severity') }}</th>
                        <th class="px-4 py-3">{{ __('reports.column_rule') }}</th>
                        <th class="px-4 py-3">{{ __('reports.column_file') }}</th>
                        <th class="px-4 py-3">{{ __('reports.column_line') }}</th>
                        <th class="px-4 py-3">{{ __('reports.column_message') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($findings as $finding)
                        <tr class="align-top">
                            <td class="px-4 py-3"><x-severity-badge :severity="$finding->severity" /></td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $finding->rule_id }}</td>
                            <td class="max-w-xs truncate px-4 py-3 text-slate-600" title="{{ $finding->file_path }}">
                                {{ $finding->file_path }}
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $finding->line ?? __('reports.value_unknown') }}</td>
                            <td class="px-4 py-3 text-slate-700">
                                <div class="max-w-md">{{ $finding->message }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $findings->links('partials.pagination') }}
        </div>
    @endif
@endsection

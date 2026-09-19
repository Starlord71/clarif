<div id="findings-results" data-fragment="findings-results">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('reports.findings_title') }}</h2>

        <form method="GET" action="{{ route('reports.show', $report) }}" data-ajax-form="findings-results"
              class="flex items-center gap-2">
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
            <noscript>
                <button type="submit"
                        class="rounded-md border border-slate-300 px-3 py-1 text-sm font-medium text-slate-600">
                    {{ __('reports.filter_apply') }}
                </button>
            </noscript>
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
                        <tr class="align-top" data-finding-trigger tabindex="0" role="button"
                            aria-label="{{ __('reports.findings_view_detail') }}: {{ $finding->rule_id }}">
                            <td class="px-4 py-3"><x-severity-badge :severity="$finding->severity" /></td>
                            <td class="px-4 py-3 font-mono text-sm text-slate-700">{{ $finding->rule_id }}</td>
                            <td class="max-w-xs truncate px-4 py-3 text-slate-600" title="{{ $finding->file_path }}">
                                {{ $finding->file_path }}
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $finding->line ?? __('reports.value_unknown') }}</td>
                            <td class="px-4 py-3 text-slate-700">
                                <div class="max-w-md truncate">{{ $finding->message }}</div>

                                @include('reports.partials.finding-detail', ['finding' => $finding])
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
</div>

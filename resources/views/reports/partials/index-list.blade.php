<div id="reports-list" data-fragment="reports-list" data-has-pending="{{ $hasPending ? '1' : '0' }}">
    @if ($reports->isEmpty())
        <div class="rounded-lg border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
            <p class="font-medium text-slate-700">{{ __('reports.reports_empty') }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ __('reports.reports_empty_hint') }}</p>
            <a href="{{ route('reports.create') }}"
               class="mt-4 inline-block rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                {{ __('reports.upload_report') }}
            </a>
        </div>
    @else
        <form method="GET" action="{{ route('reports.index') }}" data-ajax-form="reports-list"
              class="mb-3 flex items-center justify-end gap-2">
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

        <form method="GET" action="{{ route('reports.compare') }}" data-compare-form
              data-compare-url="{{ route('reports.compare') }}"
              data-selection-hint="{{ __('reports.compare_selection_hint') }}"
              data-selection-ready="{{ __('reports.compare_selection_ready') }}"
              data-selection-error="{{ __('reports.compare_selection_error') }}">
        <div class="overflow-x-auto rounded-lg border border-slate-200 shadow-sm">
            <table class="clarif-table">
                <thead>
                    <tr>
                        <th class="w-10 px-4 py-3">
                            <span class="sr-only">{{ __('reports.column_select') }}</span>
                        </th>
                        <th class="px-4 py-3">{{ __('reports.column_number') }}</th>
                        <th class="px-4 py-3">{{ __('reports.column_file') }}</th>
                        <th class="px-4 py-3">{{ __('reports.column_tool') }}</th>
                        <th class="px-4 py-3">{{ __('reports.column_status') }}</th>
                        <th class="px-4 py-3">{{ __('reports.column_findings') }}</th>
                        <th class="px-4 py-3">{{ __('reports.column_uploaded') }}</th>
                        <th class="px-4 py-3">
                            <span class="sr-only">{{ __('reports.column_actions') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $report)
                        @php
                            $number = $reports->total() - ($reports->currentPage() - 1) * $reports->perPage() - $loop->index;
                        @endphp
                        <tr data-report-trigger tabindex="0" role="button"
                            data-href="{{ route('reports.show', $report) }}"
                            aria-label="{{ __('reports.action_view') }}: {{ $report->original_filename }}">
                            <td class="px-4 py-3">
                                <input type="checkbox" name="selected[]" value="{{ $report->id }}"
                                       aria-label="{{ __('reports.select_report', ['id' => $number]) }}"
                                       class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="px-4 py-3 font-mono text-sm text-slate-500">#{{ $number }}</td>
                            <td class="max-w-xs truncate px-4 py-3 font-medium text-slate-800"
                                title="{{ $report->original_filename }}">
                                {{ $report->original_filename }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $report->tool_name }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$report->status" /></td>
                            <td class="px-4 py-3 text-slate-600">{{ $report->findings_count }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $report->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <x-delete-report :report="$report" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
            <p data-compare-hint class="text-sm text-slate-500">{{ __('reports.compare_selection_hint') }}</p>
            <button type="submit" data-compare-submit
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                {{ __('reports.compare_action') }}
            </button>
        </div>
        </form>

        <div class="mt-4">
            {{ $reports->links('partials.pagination') }}
        </div>
    @endif
</div>

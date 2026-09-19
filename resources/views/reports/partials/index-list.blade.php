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

        <div class="overflow-x-auto rounded-lg border border-slate-200 shadow-sm">
            <table class="clarif-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3">{{ __('reports.column_number') }}</th>
                        <th class="px-4 py-3">{{ __('reports.column_file') }}</th>
                        <th class="px-4 py-3">{{ __('reports.column_tool') }}</th>
                        <th class="px-4 py-3">{{ __('reports.column_status') }}</th>
                        <th class="px-4 py-3">{{ __('reports.column_findings') }}</th>
                        <th class="px-4 py-3">{{ __('reports.column_uploaded') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('reports.column_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $report)
                        @php
                            $number = $reports->total() - ($reports->currentPage() - 1) * $reports->perPage() - $loop->index;
                        @endphp
                        <tr>
                            <td class="px-4 py-3 font-mono text-sm text-slate-500">#{{ $number }}</td>
                            <td class="max-w-xs truncate px-4 py-3 font-medium text-slate-800"
                                title="{{ $report->original_filename }}">
                                {{ $report->original_filename }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $report->tool_name }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$report->status" /></td>
                            <td class="px-4 py-3 text-slate-600">{{ $report->findings_count }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $report->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-4">
                                    <a href="{{ route('reports.show', $report) }}"
                                       class="font-medium text-indigo-600 transition hover:text-indigo-800">
                                        {{ __('reports.action_view') }}
                                    </a>
                                    <x-delete-report :report="$report" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $reports->links('partials.pagination') }}
        </div>
    @endif
</div>

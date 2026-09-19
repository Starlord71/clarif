@php
    $variants = [
        'new' => [
            'border' => 'border-red-200',
            'heading' => 'text-red-700',
            'badge' => 'bg-red-50 text-red-700 ring-red-200',
        ],
        'resolved' => [
            'border' => 'border-emerald-200',
            'heading' => 'text-emerald-700',
            'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        ],
        'persistent' => [
            'border' => 'border-slate-200',
            'heading' => 'text-slate-700',
            'badge' => 'bg-slate-100 text-slate-600 ring-slate-200',
        ],
    ];

    $style = $variants[$variant];
@endphp

<section class="mb-8">
    <div class="mb-2 flex flex-wrap items-center gap-2">
        <h2 class="text-lg font-semibold {{ $style['heading'] }}">{{ $title }}</h2>
        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $style['badge'] }}">
            {{ $findings->count() }}
        </span>
    </div>
    <p class="mb-4 text-sm text-slate-500">{{ $description }}</p>

    @if ($findings->isEmpty())
        <div class="rounded-lg border border-dashed border-slate-300 bg-white px-6 py-8 text-center text-sm text-slate-500">
            {{ $emptyMessage }}
        </div>
    @else
        <div class="overflow-x-auto rounded-lg border {{ $style['border'] }} shadow-sm">
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
    @endif
</section>

{{-- Inert payload cloned into the shared finding detail modal. --}}
<template data-finding-detail>
    <div class="space-y-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="overflow-hidden rounded-md border border-slate-200">
                <p class="bg-slate-100 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-600">
                    {{ __('reports.column_severity') }}
                </p>
                <div class="px-3 py-2"><x-severity-badge :severity="$finding->severity" /></div>
            </div>
            <div class="overflow-hidden rounded-md border border-slate-200 sm:col-span-2">
                <p class="bg-slate-100 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-600">
                    {{ __('reports.column_rule') }}
                </p>
                <div class="break-all px-3 py-2 font-mono text-xs text-slate-700">{{ trim($finding->rule_id) }}</div>
            </div>
        </div>

        <div class="overflow-hidden rounded-md border border-slate-200">
            <p class="bg-slate-100 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-600">
                {{ __('reports.column_file') }}
            </p>
            <div class="break-all px-3 py-2 font-mono text-xs text-slate-700">{{ trim($finding->file_path) }}</div>
        </div>

        <div class="overflow-hidden rounded-md border border-slate-200">
            <p class="bg-slate-100 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-600">
                {{ __('reports.column_line') }}
            </p>
            <div class="px-3 py-2 font-mono text-sm text-slate-700">{{ $finding->line ?? __('reports.value_unknown') }}</div>
        </div>

        <div class="overflow-hidden rounded-md border border-slate-200">
            <p class="bg-slate-100 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-600">
                {{ __('reports.column_message') }}
            </p>
            <div class="whitespace-pre-line break-words px-3 py-2 text-sm leading-relaxed text-slate-700">{{ trim($finding->message) }}</div>
        </div>
    </div>
</template>

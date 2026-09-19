<details class="group mb-8 rounded-lg border border-slate-200 bg-white shadow-sm">
    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-sm font-medium text-slate-700 [&::-webkit-details-marker]:hidden">
        <span>{{ __('reports.compare_help_summary') }}</span>
        <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform group-open:rotate-180"
             viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"
                  clip-rule="evenodd" />
        </svg>
    </summary>

    <div class="border-t border-slate-100 px-4 py-4">
        <p class="text-sm text-slate-600">{{ __('reports.compare_help_intro') }}</p>

        <ul class="mt-4 space-y-2 text-sm text-slate-600">
            <li class="flex items-start gap-2">
                <span class="mt-0.5 inline-flex shrink-0 items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-200">
                    {{ __('reports.compare_new_title') }}
                </span>
                <span>{{ __('reports.compare_new_description') }}</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="mt-0.5 inline-flex shrink-0 items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">
                    {{ __('reports.compare_resolved_title') }}
                </span>
                <span>{{ __('reports.compare_resolved_description') }}</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="mt-0.5 inline-flex shrink-0 items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200">
                    {{ __('reports.compare_persistent_title') }}
                </span>
                <span>{{ __('reports.compare_persistent_description') }}</span>
            </li>
        </ul>

        <p class="mt-4 text-sm text-slate-500">{{ __('reports.compare_help_fingerprint') }}</p>

        <a href="{{ route('help') }}#comparar"
           class="mt-3 inline-block text-sm font-medium text-indigo-600 transition hover:text-indigo-800">
            {{ __('reports.nav_help') }} &rarr;
        </a>
    </div>
</details>

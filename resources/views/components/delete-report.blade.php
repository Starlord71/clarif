@props(['report'])

<button type="button"
        data-delete-trigger
        data-action="{{ route('reports.destroy', $report) }}"
        data-name="{{ $report->original_filename }}"
        title="{{ __('reports.action_delete') }}"
        class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-red-50 text-red-600 ring-1 ring-inset ring-red-200 transition hover:bg-red-100 hover:text-red-700 hover:ring-red-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500">
    <span class="sr-only">{{ __('reports.action_delete') }}</span>
    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd"
              d="M8.75 1a1 1 0 0 0-1 1v.5H5a.75.75 0 0 0 0 1.5h10a.75.75 0 0 0 0-1.5h-2.75V2a1 1 0 0 0-1-1h-2.5ZM6 6.75a.75.75 0 0 0-1.5 0v8.5A2.75 2.75 0 0 0 7.25 18h5.5A2.75 2.75 0 0 0 15.5 15.25v-8.5a.75.75 0 0 0-1.5 0v8.5c0 .69-.56 1.25-1.25 1.25h-5.5c-.69 0-1.25-.56-1.25-1.25v-8.5Z"
              clip-rule="evenodd" />
    </svg>
</button>

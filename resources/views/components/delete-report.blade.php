@props(['report'])

<button type="button"
        data-delete-trigger
        data-action="{{ route('reports.destroy', $report) }}"
        data-name="{{ $report->original_filename }}"
        class="font-medium text-red-600 transition hover:text-red-800">
    {{ __('reports.action_delete') }}
</button>

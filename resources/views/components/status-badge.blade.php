@props(['status'])

@php
    $classes = match ($status) {
        \App\Enums\ReportStatus::Pending => 'bg-amber-50 text-amber-700 ring-amber-200',
        \App\Enums\ReportStatus::Processing => 'bg-blue-50 text-blue-700 ring-blue-200',
        \App\Enums\ReportStatus::Completed => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        \App\Enums\ReportStatus::Failed => 'bg-red-50 text-red-700 ring-red-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset '.$classes]) }}>
    {{ $status->label() }}
</span>

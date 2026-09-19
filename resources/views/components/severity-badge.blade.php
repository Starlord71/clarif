@props(['severity'])

@php
    $classes = match ($severity) {
        \App\Enums\SarifLevel::Error => 'bg-red-50 text-red-700 ring-red-200',
        \App\Enums\SarifLevel::Warning => 'bg-amber-50 text-amber-700 ring-amber-200',
        \App\Enums\SarifLevel::Note => 'bg-sky-50 text-sky-700 ring-sky-200',
        \App\Enums\SarifLevel::None => 'bg-slate-100 text-slate-600 ring-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset '.$classes]) }}>
    {{ $severity->label() }}
</span>

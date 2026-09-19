@extends('layouts.app')

@section('title', __('reports.compare_title'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('reports.index') }}"
           class="text-sm font-medium text-slate-500 transition hover:text-slate-700">
            &larr; {{ __('reports.back_to_reports') }}
        </a>
    </div>

    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">{{ __('reports.compare_title') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('reports.compare_subtitle') }}</p>
    </div>

    @include('reports.partials.comparison-help')

    <div class="mb-8 grid grid-cols-1 items-stretch gap-3 sm:grid-cols-[1fr_auto_1fr] sm:items-center">
        @include('reports.partials.comparison-run', ['report' => $base, 'reportNumber' => $baseNumber, 'role' => __('reports.compare_base_label'), 'accent' => 'base'])

        <div class="hidden justify-center text-slate-400 sm:flex" aria-hidden="true">&rarr;</div>

        @include('reports.partials.comparison-run', ['report' => $head, 'reportNumber' => $headNumber, 'role' => __('reports.compare_head_label'), 'accent' => 'head'])
    </div>

    @include('reports.partials.comparison-section', [
        'title' => __('reports.compare_new_title'),
        'description' => __('reports.compare_new_description'),
        'emptyMessage' => __('reports.compare_new_empty'),
        'findings' => $newFindings,
        'variant' => 'new',
    ])

    @include('reports.partials.comparison-section', [
        'title' => __('reports.compare_resolved_title'),
        'description' => __('reports.compare_resolved_description'),
        'emptyMessage' => __('reports.compare_resolved_empty'),
        'findings' => $resolvedFindings,
        'variant' => 'resolved',
    ])

    @include('reports.partials.comparison-section', [
        'title' => __('reports.compare_persistent_title'),
        'description' => __('reports.compare_persistent_description'),
        'emptyMessage' => __('reports.compare_persistent_empty'),
        'findings' => $persistentFindings,
        'variant' => 'persistent',
    ])

    <p class="rounded-md border border-slate-200 bg-white px-4 py-3 text-xs text-slate-500">
        {{ __('reports.compare_line_drift_note') }}
    </p>
@endsection

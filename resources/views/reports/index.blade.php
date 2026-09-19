@extends('layouts.app')

@section('title', __('reports.reports_title'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('reports.reports_title') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('reports.reports_subtitle') }}</p>
        </div>
        <a href="{{ route('reports.create') }}"
           class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
            {{ __('reports.upload_report') }}
        </a>
    </div>

    @include('reports.partials.index-list')
@endsection

@extends('layouts.app')

@section('title', __('reports.upload_title'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-semibold text-slate-900">{{ __('reports.upload_heading') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('reports.upload_description') }}</p>

        @if ($errors->any())
            <div class="mt-4 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data"
              class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @csrf

            <label for="report" class="block text-sm font-medium text-slate-700">
                {{ __('reports.upload_field_label') }}
            </label>
            <div data-file-wrapper class="mt-2 flex items-center gap-3">
                <input type="file" name="report" id="report" accept=".sarif,.json"
                       data-file-input class="sr-only" />
                <label for="report"
                       class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                    {{ __('reports.upload_choose_file') }}
                </label>
                <span data-file-name data-empty-label="{{ __('reports.upload_no_file') }}"
                      class="truncate text-sm text-slate-500">
                    {{ __('reports.upload_no_file') }}
                </span>
            </div>
            <p class="mt-2 text-xs text-slate-500">
                {{ __('reports.upload_formats_hint', ['size' => round($maxKilobytes / 1024)]) }}
            </p>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                    {{ __('reports.upload_submit') }}
                </button>
                <a href="{{ route('reports.index') }}"
                   class="text-sm font-medium text-slate-500 transition hover:text-slate-700">
                    {{ __('reports.back_to_reports') }}
                </a>
            </div>
        </form>

        <div class="mt-6 rounded-lg bg-slate-100 p-4 text-sm text-slate-600">
            <p class="font-medium text-slate-700">{{ __('reports.upload_tips_title') }}</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                <li>{{ __('reports.upload_tip_one') }}</li>
                <li>{{ __('reports.upload_tip_two') }}</li>
                <li>{{ __('reports.upload_tip_three') }}</li>
            </ul>
        </div>
    </div>
@endsection

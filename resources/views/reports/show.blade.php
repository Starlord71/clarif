@extends('layouts.app')

@section('title', __('reports.report_title', ['id' => $reportNumber]))

@section('content')
    <div class="mb-6">
        <a href="{{ route('reports.index') }}"
           class="text-sm font-medium text-slate-500 transition hover:text-slate-700">
            &larr; {{ __('reports.back_to_reports') }}
        </a>
    </div>

    @include('reports.partials.show-status')

    <form method="GET" action="{{ route('reports.show', $report) }}" data-findings-filters
          class="mb-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <p class="mb-3 text-sm font-medium text-slate-700">{{ __('reports.filters_title') }}</p>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="severity" class="block text-xs font-medium text-slate-500">
                    {{ __('reports.filter_severity') }}
                </label>
                <select name="severity" id="severity"
                        class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                    <option value="">{{ __('reports.filter_all') }}</option>
                    @foreach (\App\Enums\SarifLevel::cases() as $level)
                        <option value="{{ $level->value }}" @selected($severity === $level->value)>
                            {{ $level->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="rule_id" class="block text-xs font-medium text-slate-500">
                    {{ __('reports.filter_rule') }}
                </label>
                <input type="text" name="rule_id" id="rule_id" value="{{ $ruleId }}"
                       placeholder="{{ __('reports.filter_rule_placeholder') }}"
                       class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700" />
            </div>
            <div>
                <label for="file_path" class="block text-xs font-medium text-slate-500">
                    {{ __('reports.filter_file') }}
                </label>
                <input type="text" name="file_path" id="file_path" value="{{ $filePath }}"
                       placeholder="{{ __('reports.filter_file_placeholder') }}"
                       class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700" />
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                    {{ __('reports.filter_apply') }}
                </button>
                <a href="{{ route('reports.show', $report) }}" data-filters-reset
                   class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100">
                    {{ __('reports.filter_reset') }}
                </a>
            </div>
        </div>
    </form>

    @include('reports.partials.findings-results')
@endsection

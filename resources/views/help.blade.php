@extends('layouts.app')

@section('title', __('help.title'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <h1 class="text-3xl font-semibold text-slate-900">{{ __('help.title') }}</h1>
        <p class="mt-1 text-slate-500">{{ __('help.subtitle') }}</p>

        <section id="flujo" class="mt-10 scroll-mt-20">
            <h2 class="text-xl font-semibold text-slate-900">{{ __('help.workflow_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('help.workflow_intro') }}</p>

            <ol class="mt-4 space-y-3">
                @foreach ([__('help.workflow_step_one'), __('help.workflow_step_two'), __('help.workflow_step_three')] as $step)
                    <li class="flex gap-3 rounded-lg bg-white p-4 text-sm text-slate-600 shadow-sm ring-1 ring-slate-200">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-semibold text-white">
                            {{ $loop->iteration }}
                        </span>
                        <span>{{ $step }}</span>
                    </li>
                @endforeach
            </ol>
        </section>

        <section id="comparar" class="mt-10 scroll-mt-20">
            <h2 class="text-xl font-semibold text-slate-900">{{ __('help.compare_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('help.compare_intro') }}</p>

            <h3 class="mt-6 font-medium text-slate-800">{{ __('help.compare_how_title') }}</h3>
            <p class="mt-2 text-slate-600">{{ __('help.compare_how_body') }}</p>

            <ul class="mt-4 space-y-3">
                <li class="rounded-lg border border-red-200 bg-white p-4">
                    <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-200">
                        {{ __('help.compare_new_title') }}
                    </span>
                    <p class="mt-2 text-sm text-slate-600">{{ __('help.compare_new_body') }}</p>
                </li>
                <li class="rounded-lg border border-emerald-200 bg-white p-4">
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">
                        {{ __('help.compare_resolved_title') }}
                    </span>
                    <p class="mt-2 text-sm text-slate-600">{{ __('help.compare_resolved_body') }}</p>
                </li>
                <li class="rounded-lg border border-slate-200 bg-white p-4">
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200">
                        {{ __('help.compare_persistent_title') }}
                    </span>
                    <p class="mt-2 text-sm text-slate-600">{{ __('help.compare_persistent_body') }}</p>
                </li>
            </ul>

            <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <h3 class="font-medium text-amber-800">{{ __('help.compare_drift_title') }}</h3>
                <p class="mt-1 text-sm text-amber-700">{{ __('help.compare_drift_body') }}</p>
            </div>
        </section>

        <div class="mt-10 border-t border-slate-200 pt-6">
            <a href="{{ route('reports.index') }}"
               class="text-sm font-medium text-indigo-600 transition hover:text-indigo-800">
                {{ __('reports.back_to_reports') }}
            </a>
        </div>
    </div>
@endsection

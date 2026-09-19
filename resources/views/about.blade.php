@extends('layouts.app')

@section('title', __('about.title'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <h1 class="text-3xl font-semibold text-slate-900">{{ __('about.title') }}</h1>

        <div class="mt-4 space-y-4 text-slate-600">
            <p>{{ __('about.intro_1') }}</p>
            <p>{{ __('about.intro_2') }}</p>
        </div>

        <section class="mt-10">
            <h2 class="text-xl font-semibold text-slate-900">{{ __('about.tools_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('about.tools_intro') }}</p>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <h3 class="font-medium text-slate-800">CodeQL</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ __('about.tools_codeql') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <h3 class="font-medium text-slate-800">ESLint</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ __('about.tools_eslint') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <h3 class="font-medium text-slate-800">Semgrep</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ __('about.tools_semgrep') }}</p>
                </div>
            </div>
        </section>

        <section class="mt-10">
            <h2 class="text-xl font-semibold text-slate-900">{{ __('about.scope_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('about.scope_intro') }}</p>

            <ul class="mt-4 space-y-3">
                <li class="flex gap-3 rounded-lg bg-white p-4 text-sm text-slate-600 shadow-sm ring-1 ring-slate-200">
                    <span class="font-mono text-slate-400">runs[0]</span>
                    <span>{{ __('about.scope_runs') }}</span>
                </li>
                <li class="flex gap-3 rounded-lg bg-white p-4 text-sm text-slate-600 shadow-sm ring-1 ring-slate-200">
                    <span class="font-mono text-slate-400">codeFlows</span>
                    <span>{{ __('about.scope_codeflows') }}</span>
                </li>
                <li class="flex gap-3 rounded-lg bg-white p-4 text-sm text-slate-600 shadow-sm ring-1 ring-slate-200">
                    <span class="font-mono text-slate-400">level</span>
                    <span>{{ __('about.scope_severity') }}</span>
                </li>
            </ul>
        </section>

        <section class="mt-10">
            <h2 class="text-xl font-semibold text-slate-900">{{ __('about.diffing_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('about.diffing_intro') }}</p>
            <a href="{{ route('help') }}#comparar"
               class="mt-3 inline-block text-sm font-medium text-indigo-600 transition hover:text-indigo-800">
                {{ __('reports.nav_help') }} &rarr;
            </a>
        </section>

        <div class="mt-10 border-t border-slate-200 pt-6">
            <a href="{{ route('reports.index') }}"
               class="text-sm font-medium text-indigo-600 transition hover:text-indigo-800">
                {{ __('reports.back_to_reports') }}
            </a>
        </div>
    </div>
@endsection

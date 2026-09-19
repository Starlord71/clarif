<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', __('reports.brand')) · {{ __('reports.brand') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
        @php
            $navBase = 'rounded-md px-3 py-1.5 text-sm font-medium transition';
            $navIdle = 'text-slate-300 hover:bg-white/10 hover:text-white';
            $navActive = 'bg-white/10 text-white';
        @endphp

        <header class="bg-slate-900">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3">
                <a href="{{ route('reports.index') }}" class="text-lg font-semibold tracking-tight text-white">
                    {{ __('reports.brand') }}
                </a>

                <div class="flex flex-wrap items-center gap-3">
                    <nav class="flex items-center gap-1">
                        <a href="{{ route('reports.index') }}"
                           class="{{ $navBase }} {{ request()->routeIs('reports.index', 'reports.show') ? $navActive : $navIdle }}">
                            {{ __('reports.nav_reports') }}
                        </a>
                        <a href="{{ route('reports.create') }}"
                           class="{{ $navBase }} {{ request()->routeIs('reports.create') ? $navActive : $navIdle }}">
                            {{ __('reports.nav_upload') }}
                        </a>
                        <a href="{{ route('about') }}"
                           class="{{ $navBase }} {{ request()->routeIs('about') ? $navActive : $navIdle }}">
                            {{ __('reports.nav_about') }}
                        </a>
                    </nav>

                    <div class="flex items-center gap-1 rounded-md bg-white/5 p-0.5"
                         aria-label="{{ __('reports.language_label') }}">
                        @foreach (config('clarif.supported_locales', ['en', 'es']) as $locale)
                            <a href="{{ route('lang', $locale) }}"
                               @class([
                                   'rounded px-2.5 py-1 text-xs font-semibold uppercase transition',
                                   'bg-white text-slate-900' => app()->getLocale() === $locale,
                                   'text-slate-300 hover:bg-white/10 hover:text-white' => app()->getLocale() !== $locale,
                               ])>
                                {{ $locale }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            @if (session('status'))
                <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>

        <div data-modal="delete-report" role="dialog" aria-modal="true"
             aria-labelledby="delete-report-title"
             class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 id="delete-report-title" class="text-lg font-semibold text-slate-900">
                    {{ __('reports.delete_modal_title') }}
                </h3>
                <p class="mt-2 text-sm text-slate-600">{{ __('reports.delete_modal_body') }}</p>
                <p data-modal-name class="mt-2 truncate text-sm font-medium text-slate-800"></p>

                <form method="POST" data-modal-form class="mt-6 flex justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" data-modal-cancel
                            class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100">
                        {{ __('reports.delete_cancel') }}
                    </button>
                    <button type="submit"
                            class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700">
                        {{ __('reports.delete_confirm_yes') }}
                    </button>
                </form>
            </div>
        </div>
    </body>
</html>

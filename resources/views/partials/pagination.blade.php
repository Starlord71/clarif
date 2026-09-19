@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('reports.pagination_navigation') }}" data-pagination
         class="flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-sm text-slate-500">
            {{ __('reports.pagination_summary', [
                'first' => $paginator->firstItem(),
                'last' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ]) }}
        </p>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="cursor-not-allowed rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-400">
                    {{ __('reports.pagination_previous') }}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-600 transition hover:bg-slate-100">
                    {{ __('reports.pagination_previous') }}
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-sm text-slate-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                               aria-label="{{ __('reports.pagination_goto', ['page' => $page]) }}"
                               class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-600 transition hover:bg-slate-100">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-600 transition hover:bg-slate-100">
                    {{ __('reports.pagination_next') }}
                </a>
            @else
                <span class="cursor-not-allowed rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-400">
                    {{ __('reports.pagination_next') }}
                </span>
            @endif
        </div>
    </nav>
@endif

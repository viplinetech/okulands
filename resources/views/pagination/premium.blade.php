{{-- Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com) --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-center gap-1.5 sm:gap-2">
        @if ($paginator->onFirstPage())
            <span class="flex h-11 w-11 items-center justify-center rounded-full border border-ink/10 text-mute/40" aria-hidden="true"><x-icon name="arrow" class="h-4 w-4 rotate-180" /></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page" class="flex h-11 w-11 items-center justify-center rounded-full border border-ink/20 text-ink transition hover:bg-ink hover:text-page"><x-icon name="arrow" class="h-4 w-4 rotate-180" /></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="hidden px-1 text-mute sm:inline">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="flex h-11 min-w-11 items-center justify-center rounded-full bg-ink px-3 text-sm font-bold text-page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" aria-label="Go to page {{ $page }}" class="hidden h-11 min-w-11 items-center justify-center rounded-full px-3 text-sm font-semibold text-ink transition hover:bg-ink/10 sm:flex">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page" class="flex h-11 w-11 items-center justify-center rounded-full border border-ink/20 text-ink transition hover:bg-ink hover:text-page"><x-icon name="arrow" class="h-4 w-4" /></a>
        @else
            <span class="flex h-11 w-11 items-center justify-center rounded-full border border-ink/10 text-mute/40" aria-hidden="true"><x-icon name="arrow" class="h-4 w-4" /></span>
        @endif
    </nav>
    <p class="mt-4 text-center text-xs text-mute sm:hidden">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</p>
@endif

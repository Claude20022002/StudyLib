@props([
    'paginator' => null,
])

@if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator)
    <nav {{ $attributes->merge(['class' => 'sl-pagination']) }} aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <button type="button" class="sl-page-btn" disabled aria-label="Précédent">
                <x-ui.icon name="chevron-left" class="h-4 w-4" />
            </button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="sl-page-btn" wire:navigate aria-label="Précédent">
                <x-ui.icon name="chevron-left" class="h-4 w-4" />
            </a>
        @endif

        @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            @if ($page == $paginator->currentPage())
                <span class="sl-page-btn is-active" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="sl-page-btn" wire:navigate>{{ $page }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="sl-page-btn" wire:navigate aria-label="Suivant">
                <x-ui.icon name="chevron-right" class="h-4 w-4" />
            </a>
        @else
            <button type="button" class="sl-page-btn" disabled aria-label="Suivant">
                <x-ui.icon name="chevron-right" class="h-4 w-4" />
            </button>
        @endif
    </nav>
@else
    <nav {{ $attributes->merge(['class' => 'sl-pagination']) }} aria-label="Pagination">
        {{ $slot }}
    </nav>
@endif

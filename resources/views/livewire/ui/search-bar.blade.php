<div {{ $attributes->merge(['class' => 'sl-search']) }} role="search">
    <x-ui.icon name="search" class="sl-search-ico" />
    <input
        type="search"
        wire:model.live.debounce.300ms="query"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        aria-label="{{ $placeholder }}"
    />
    @if ($showShortcut)
        <kbd class="sl-search-kbd sl-desktop-only" aria-hidden="true">Ctrl+K</kbd>
    @endif
</div>

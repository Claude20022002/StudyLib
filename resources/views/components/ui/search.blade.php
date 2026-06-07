@props([
    'placeholder' => 'Rechercher un cours, un module, un auteur…',
    'showShortcut' => true,
    'name' => 'q',
])

<div {{ $attributes->merge(['class' => 'sl-search']) }} role="search">
    <x-ui.icon name="search" class="sl-search-ico" />
    <input
        type="search"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        aria-label="{{ $placeholder }}"
        {{ $attributes->except('class') }}
    />
    @if ($showShortcut)
        <kbd class="sl-search-kbd sl-desktop-only" aria-hidden="true">Ctrl+K</kbd>
    @endif
</div>

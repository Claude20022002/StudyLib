@props([
    'href' => null,
    'type' => null,
    'title' => '',
])

@if ($href)
    <a href="{{ $href }}" wire:navigate {{ $attributes->merge(['class' => 'sl-card']) }}>
        @include('components.ui.partials.card-inner')
    </a>
@else
    <article {{ $attributes->merge(['class' => 'sl-card']) }}>
        @include('components.ui.partials.card-inner')
    </article>
@endif

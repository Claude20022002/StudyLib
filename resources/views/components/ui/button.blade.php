@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
    $classes = collect([
        'sl-btn',
        match ($variant) {
            'primary' => 'sl-btn--primary',
            'secondary' => 'sl-btn--secondary',
            'ghost' => 'sl-btn--ghost',
            'danger' => 'sl-btn--danger',
            'success' => 'sl-btn--success',
            default => 'sl-btn--primary',
        },
        match ($size) {
            'sm' => 'sl-btn--sm',
            'lg' => 'sl-btn--lg',
            'xs' => 'sl-btn--xs',
            default => '',
        },
    ])->filter()->implode(' ');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif

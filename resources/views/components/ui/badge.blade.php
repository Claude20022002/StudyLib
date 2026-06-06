@props([
    'variant' => 'neutral',
    'dot' => false,
])

@php
    $classes = collect([
        'sl-badge',
        match ($variant) {
            'primary' => 'sl-badge--primary',
            'success' => 'sl-badge--success',
            'warning' => 'sl-badge--warning',
            'danger' => 'sl-badge--danger',
            default => 'sl-badge--neutral',
        },
    ])->implode(' ');
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if ($dot)
        <span class="sl-dot" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>

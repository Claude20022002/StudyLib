@props([
    'variant' => 'info',
    'title' => '',
    'text' => '',
    'dismissible' => false,
])

@php
    $iconVariant = match ($variant) {
        'success' => 'check',
        'warning' => 'alert',
        'danger' => 'x',
        default => 'info',
    };
@endphp

<div {{ $attributes->merge(['class' => 'sl-toast']) }} role="status">
    <div @class(['sl-toast-ico', 'sl-toast-ico--'.$variant])>
        <x-ui.icon :name="$iconVariant" class="h-5 w-5" />
    </div>
    <div class="sl-toast-body">
        @if ($title)
            <div class="sl-toast-title">{{ $title }}</div>
        @endif
        @if ($text)
            <div class="sl-toast-text">{{ $text }}</div>
        @endif
        {{ $slot }}
    </div>
    @if ($dismissible)
        <button type="button" class="sl-toast-close" aria-label="Fermer" @click="$el.closest('.sl-toast').remove()">
            <x-ui.icon name="x" class="h-3.5 w-3.5" />
        </button>
    @endif
</div>

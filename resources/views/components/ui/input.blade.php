@props([
    'type' => 'text',
    'error' => false,
    'leadingIcon' => null,
    'size' => 'md',
])

@php
    $inputClass = collect([
        'sl-input',
        $size === 'lg' ? 'sl-input--lg' : '',
        $error ? 'is-error' : '',
    ])->filter()->implode(' ');
@endphp

<div class="relative">
    @if ($leadingIcon)
        <span class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-muted" aria-hidden="true">
            <x-ui.icon :name="$leadingIcon" class="h-[18px] w-[18px]" />
        </span>
    @endif

    <input
        type="{{ $type }}"
        {{ $attributes->merge(['class' => $inputClass]) }}
    />
</div>

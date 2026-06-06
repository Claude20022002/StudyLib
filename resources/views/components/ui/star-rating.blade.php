@props([
    'value' => 0,
    'max' => 5,
    'interactive' => false,
    'label' => null,
])

@php
    $labelText = $label ?? "Note : {$value} sur {$max}";
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }} role="img" aria-label="{{ $labelText }}">
    <span class="inline-flex gap-px">
        @for ($i = 1; $i <= $max; $i++)
            <svg
                @class(['h-3.5 w-3.5', $i <= $value ? 'text-star' : 'text-border-strong'])
                viewBox="0 0 24 24"
                fill="currentColor"
                aria-hidden="true"
            >
                <path d="M12 2.5l2.9 5.9 6.5.95-4.7 4.58 1.1 6.47L12 17.4l-5.8 3.05 1.1-6.47L2.6 9.35l6.5-.95z"/>
            </svg>
        @endfor
    </span>
    {{ $slot }}
</div>

@props([
    'initials' => '?',
    'size' => 'md',
])

<span {{ $attributes->merge([
    'class' => 'sl-avatar'.($size === 'sm' ? ' sl-avatar--sm' : ''),
    'aria-hidden' => 'true',
]) }}>
    {{ $initials }}
</span>

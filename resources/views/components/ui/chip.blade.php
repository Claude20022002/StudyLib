@props([
    'label' => '',
    'active' => false,
])

<button
    type="button"
    {{ $attributes->merge([
        'class' => 'sl-chip'.($active ? ' is-active' : ''),
        'aria-pressed' => $active ? 'true' : 'false',
    ]) }}
>
    {{ $label ?: $slot }}
</button>

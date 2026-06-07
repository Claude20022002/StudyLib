@props([
    'class' => '',
])

<img
    src="{{ asset('build/assets/hand.png') }}"
    alt=""
    aria-hidden="true"
    loading="lazy"
    decoding="async"
    {{ $attributes->merge(['class' => trim('sl-greet-icon '.$class)]) }}
/>

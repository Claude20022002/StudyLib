@props([
    'class' => '',
])

<img
    src="{{ asset(config('flaticon.icons.hand', 'images/hand.png')) }}"
    alt=""
    aria-hidden="true"
    loading="lazy"
    decoding="async"
    {{ $attributes->merge(['class' => trim('sl-greet-icon '.$class)]) }}
/>

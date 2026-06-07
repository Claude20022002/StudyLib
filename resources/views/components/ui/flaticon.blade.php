@props([
    'name',
    'class' => '',
])

@php
    $path = config("flaticon.icons.{$name}");
@endphp

@if ($path)
    <img
        src="{{ asset($path) }}"
        alt=""
        aria-hidden="true"
        loading="lazy"
        decoding="async"
        {{ $attributes->merge(['class' => trim('sl-flaticon '.$class)]) }}
    />
@endif

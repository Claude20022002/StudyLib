@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'id' => null,
])

@php
    $inputId = $id ?? $attributes->get('id');
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'sl-field']) }}>
    @if ($label)
        <label @if($inputId) for="{{ $inputId }}" @endif>{{ $label }}</label>
    @endif

    {{ $slot }}

    @if ($error)
        <p @if($inputId) id="{{ $inputId }}-error" @endif class="sl-field-error" role="alert">{{ $error }}</p>
    @endif

    @if ($hint && ! $error)
        <p @if($inputId) id="{{ $inputId }}-hint" @endif class="sl-field-hint">{{ $hint }}</p>
    @endif
</div>

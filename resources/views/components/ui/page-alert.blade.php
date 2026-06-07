@props([
    'variant' => 'success',
    'title' => null,
])

@php
    $variantClass = match ($variant) {
        'danger' => 'sl-page-alert--danger',
        default => 'sl-page-alert--success',
    };
    $role = $variant === 'danger' ? 'alert' : 'status';
@endphp

<div {{ $attributes->merge(['class' => "sl-page-alert {$variantClass}"]) }} role="{{ $role }}">
    <div class="sl-page-alert__ico" aria-hidden="true">
        @if ($variant === 'danger')
            <x-ui.icon name="alert" class="h-[18px] w-[18px]" />
        @else
            <x-ui.icon name="check" class="h-[18px] w-[18px]" />
        @endif
    </div>
    <div class="min-w-0 flex-1">
        @if ($title)
            <div class="sl-page-alert__title">{{ $title }}</div>
        @endif
        <div @class(['sl-page-alert__text', 'font-semibold' => ! $title])>{{ $slot }}</div>
    </div>
</div>

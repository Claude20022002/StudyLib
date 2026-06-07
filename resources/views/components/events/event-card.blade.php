@props([
    'event',
    'eventService',
    'variant' => 'list',
])

@php
    $typeKey = $eventService->typeKey($event);
    $duration = $eventService->formatDuration($event);
@endphp

@if ($variant === 'calendar')
    <button
        type="button"
        wire:click="openDetail('{{ $event->id }}')"
        @class(['sl-ev-cal-chip', 'sl-ev-type--'.$typeKey])
        title="{{ $event->title }}"
    >
        <span class="sl-ev-cal-chip__dot sl-ev-dot--{{ $typeKey }}" aria-hidden="true"></span>
        <span class="sl-ev-cal-chip__label">{{ $event->title }}</span>
    </button>
@else
    <article
        role="button"
        tabindex="0"
        wire:click="openDetail('{{ $event->id }}')"
        wire:keydown.enter="openDetail('{{ $event->id }}')"
        @class(['sl-ev-card', 'sl-ev-card--'.$typeKey])
    >
        <div class="sl-ev-card__time">
            <div class="sl-ev-card__clock">{{ $eventService->formatTime($event) }}</div>
            @if ($duration)
                <div class="sl-ev-card__dur">{{ $duration }}</div>
            @endif
        </div>

        <div class="sl-ev-card__body">
            <div class="sl-ev-card__tags">
                <span @class(['sl-ev-badge', 'sl-ev-type--'.$typeKey])>{{ $eventService->typeLabel($event) }}</span>
            </div>
            <h3 class="sl-ev-card__title">{{ $event->title }}</h3>
            <div class="sl-ev-card__meta">
                @if ($event->location)
                    <span class="sl-ev-card__meta-item">
                        <x-ui.icon name="map-pin" class="h-3.5 w-3.5" />
                        {{ $event->location }}
                    </span>
                @endif
                <span class="sl-ev-card__meta-item">
                    <x-ui.icon name="user" class="h-3.5 w-3.5" />
                    {{ $eventService->maskedOrganizerName($event) }}
                </span>
            </div>
        </div>

        <div class="sl-ev-card__aside">
            <span class="sl-card-cta">
                Voir
                <x-ui.icon name="chevron-right" class="h-4 w-4" />
            </span>
        </div>
    </article>
@endif

@if ($detailOpen && $detail)
    <button type="button" class="sl-lib-scrim is-open" wire:click="closeDetail" aria-label="Fermer le détail de l'événement"></button>
@endif

<aside
    @class(['sl-lib-drawer', 'is-open' => $detailOpen && $detail])
    role="dialog"
    aria-modal="true"
    aria-label="Détail de l'événement"
>
    @if ($detail)
        @php
            $typeKey = $eventService->typeKey($detail);
            $duration = $eventService->formatDuration($detail);
        @endphp

        <div class="sl-lib-drawer__head">
            <span class="text-sm font-semibold text-muted">Détail de l'événement</span>
            <button type="button" class="sl-icon-btn" wire:click="closeDetail" aria-label="Fermer">
                <x-ui.icon name="x" class="h-5 w-5" />
            </button>
        </div>

        <div class="sl-lib-drawer__body">
            <div class="sl-ev-det-tags">
                <span @class(['sl-ev-badge', 'sl-ev-type--'.$typeKey])>{{ $eventService->typeLabel($detail) }}</span>
            </div>

            <h2 class="sl-ev-det-title">{{ $detail->title }}</h2>

            <div class="sl-ev-det-quick">
                <div class="sl-ev-det-quick__item">
                    <x-ui.icon name="calendar" class="h-[18px] w-[18px] text-primary" />
                    <div>
                        <div class="sl-ev-det-quick__key">Date</div>
                        <div class="sl-ev-det-quick__val">{{ $detail->starts_at->locale('fr')->translatedFormat('d F Y') }}</div>
                    </div>
                </div>
                <div class="sl-ev-det-quick__item">
                    <x-ui.icon name="clock" class="h-[18px] w-[18px] text-primary" />
                    <div>
                        <div class="sl-ev-det-quick__key">Horaire</div>
                        <div class="sl-ev-det-quick__val">
                            {{ $eventService->formatTime($detail) }}
                            @if ($detail->ends_at)
                                · {{ $detail->ends_at->format('H:i') }}
                            @endif
                            @if ($duration)
                                <span class="text-muted">({{ $duration }})</span>
                            @endif
                        </div>
                    </div>
                </div>
                @if ($detail->location)
                    <div class="sl-ev-det-quick__item">
                        <x-ui.icon name="map-pin" class="h-[18px] w-[18px] text-primary" />
                        <div>
                            <div class="sl-ev-det-quick__key">Lieu</div>
                            <div class="sl-ev-det-quick__val">{{ $detail->location }}</div>
                        </div>
                    </div>
                @endif
                <div class="sl-ev-det-quick__item">
                    <x-ui.icon name="user" class="h-[18px] w-[18px] text-primary" />
                    <div>
                        <div class="sl-ev-det-quick__key">Organisateur</div>
                        <div class="sl-ev-det-quick__val">{{ $eventService->maskedOrganizerName($detail) }}</div>
                    </div>
                </div>
            </div>

            @if ($detail->description)
                <section class="sl-ev-det-section">
                    <h3 class="sl-ev-det-section__title">Description</h3>
                    <p class="text-sm leading-relaxed text-ink-soft">{{ $detail->description }}</p>
                </section>
            @endif
        </div>

        <div class="sl-lib-drawer__foot">
            <button type="button" wire:click="closeDetail" class="sl-btn sl-btn--secondary">Fermer</button>
            <button type="button" wire:click="closeDetail" class="sl-btn sl-btn--primary">
                <x-ui.icon name="check" class="h-4 w-4" />
                Noté dans mon agenda
            </button>
        </div>
    @endif
</aside>

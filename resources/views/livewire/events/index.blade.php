<div>
    <section class="sl-ev-hero" aria-label="Introduction événements">
        <div class="sl-ev-hero__ico" aria-hidden="true">
            <x-ui.icon name="calendar" class="h-5 w-5" />
        </div>
        <div class="min-w-0 flex-1">
            <h2 class="text-h4 leading-tight font-bold tracking-tight">Agenda du campus</h2>
            <p class="mt-1 text-sm text-muted">Hackathons, conférences, soutenances et forums · tout ce qui se passe à HESTIM.</p>
        </div>
        <div class="sl-ev-hero__search">
            <x-ui.icon name="search" class="sl-ev-hero__search-ico" />
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher un événement…"
                class="sl-ev-hero__input"
                aria-label="Rechercher un événement"
            />
            @if ($search !== '')
                <button type="button" wire:click="clearSearch" class="sl-ev-hero__clear" aria-label="Effacer la recherche">
                    <x-ui.icon name="x" class="h-4 w-4" />
                </button>
            @endif
        </div>
    </section>

    <div class="sl-ev-toolbar">
        <div class="sl-ev-month-nav">
            <button type="button" wire:click="previousMonth" class="sl-ev-nav-btn" aria-label="Mois précédent">
                <x-ui.icon name="chevron-left" class="h-[18px] w-[18px]" />
            </button>
            <h2 class="sl-ev-month-title">{{ $monthTitle }}</h2>
            <button type="button" wire:click="nextMonth" class="sl-ev-nav-btn" aria-label="Mois suivant">
                <x-ui.icon name="chevron-right" class="h-[18px] w-[18px]" />
            </button>
        </div>

        <button type="button" wire:click="goToToday" class="sl-btn sl-btn--secondary sl-ev-today-btn">Aujourd'hui</button>

        <div class="flex-1"></div>

        <div class="sl-ev-view-toggle sl-desktop-only">
            <button
                type="button"
                wire:click="setViewMode('calendar')"
                @class(['sl-ev-vt-btn', 'is-active' => $viewMode === 'calendar'])
            >
                <x-ui.icon name="calendar" class="h-[15px] w-[15px]" />
                Calendrier
            </button>
            <button
                type="button"
                wire:click="setViewMode('list')"
                @class(['sl-ev-vt-btn', 'is-active' => $viewMode === 'list'])
            >
                <x-ui.icon name="list" class="h-[15px] w-[15px]" />
                Liste
            </button>
        </div>
    </div>

    <div class="sl-ev-legend" aria-label="Types d'événements">
        <span class="sl-ev-legend__item"><span class="sl-ev-dot sl-ev-dot--hackathon"></span>Hackathon</span>
        <span class="sl-ev-legend__item"><span class="sl-ev-dot sl-ev-dot--conference"></span>Conférence</span>
        <span class="sl-ev-legend__item"><span class="sl-ev-dot sl-ev-dot--soutenance"></span>Soutenance</span>
        <span class="sl-ev-legend__item"><span class="sl-ev-dot sl-ev-dot--portes"></span>Portes ouvertes</span>
    </div>

    <div wire:loading.class="opacity-60">
        @if ($monthEvents->isEmpty())
            <x-ui.empty-state
                title="Aucun événement ce mois-ci"
                :description="$search !== '' ? 'Aucun résultat pour votre recherche. Essayez un autre mot-clé ou changez de mois.' : 'Aucun événement planifié pour cette période. Revenez plus tard ou consultez un autre mois.'"
            >
                <x-slot:icon>
                    <x-ui.icon name="calendar" class="h-[30px] w-[30px]" />
                </x-slot:icon>
                <div class="flex flex-wrap justify-center gap-3">
                    @if ($search !== '')
                        <button type="button" wire:click="clearSearch" class="sl-btn sl-btn--secondary">Effacer la recherche</button>
                    @endif
                    <button type="button" wire:click="goToToday" class="sl-btn sl-btn--primary">Revenir à aujourd'hui</button>
                </div>
            </x-ui.empty-state>
        @else
            <div @class(['sl-ev-calendar', 'is-hidden' => $viewMode === 'list'])>
                <div class="sl-ev-cal-dow">
                    <span>Lun</span>
                    <span>Mar</span>
                    <span>Mer</span>
                    <span>Jeu</span>
                    <span>Ven</span>
                    <span>Sam</span>
                    <span>Dim</span>
                </div>
                <div class="sl-ev-cal-grid">
                    @foreach ($calendarCells as $cell)
                        <div @class(['sl-ev-cal-cell', 'is-out' => $cell['out'], 'is-today' => $cell['is_today']])>
                            <span @class(['sl-ev-cal-num', 'is-today' => $cell['is_today']])>{{ $cell['day'] }}</span>
                            @foreach (array_slice($cell['events'], 0, 3) as $event)
                                <x-events.event-card :event="$event" :event-service="$eventService" variant="calendar" />
                            @endforeach
                            @if (count($cell['events']) > 3)
                                <button
                                    type="button"
                                    class="sl-ev-cal-more"
                                    wire:click="setViewMode('list')"
                                >
                                    +{{ count($cell['events']) - 3 }} de plus
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div @class(['sl-ev-list', 'is-visible' => $viewMode === 'list'])>
                @foreach ($dayGroups as $group)
                    <section class="sl-ev-day-group">
                        <header class="sl-ev-day-head">
                            <span class="sl-ev-day-num">{{ $group['date']->format('d') }}</span>
                            <span class="sl-ev-day-wd">{{ ucfirst($group['weekday']) }}</span>
                            <span class="sl-ev-day-rest">{{ $group['date']->translatedFormat('F Y') }}</span>
                        </header>
                        <div class="sl-ev-day-events">
                            @foreach ($group['events'] as $event)
                                <x-events.event-card :event="$event" :event-service="$eventService" variant="list" />
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>

    @include('partials.events.detail-drawer', [
        'detailOpen' => $detailOpen,
        'detail' => $detail,
        'eventService' => $eventService,
    ])
</div>

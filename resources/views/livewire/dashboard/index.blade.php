<div wire:init="loadDashboard">
    {{-- Hero --}}
    <div class="sl-dashboard-hero">
        <div>
            <h1 class="sl-dashboard-greet">
                <span class="sl-greet-line">
                    Bonjour {{ $overview['greeting_name'] ?? Auth::user()?->name }}
                    <x-ui.icon name="wave" class="sl-greet-icon" aria-hidden="true" />
                </span>
            </h1>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                @if (! empty($overview['filiere_name']))
                    <x-ui.badge variant="primary">{{ $overview['filiere_name'] }}</x-ui.badge>
                @endif
                @if (! empty($overview['year_level']))
                    <x-ui.badge variant="neutral">L{{ $overview['year_level'] }}</x-ui.badge>
                @endif
                @if (! empty($overview['date_label']))
                    <span class="text-sm text-muted">· {{ ucfirst($overview['date_label']) }}</span>
                @endif
            </div>
        </div>
        <x-ui.button variant="primary" href="{{ route('documents.index') }}" class="sl-desktop-only">
            <x-ui.icon name="plus" />
            Déposer un document
        </x-ui.button>
    </div>

    {{-- KPIs --}}
    @if (! $ready)
        <div class="sl-kpi-grid sl-skeleton" aria-hidden="true">
            @for ($i = 0; $i < 4; $i++)
                <div class="sl-sk sl-sk-kpi"></div>
            @endfor
        </div>
    @else
        <div class="sl-kpi-grid">
            @foreach ($overview['kpis'] ?? [] as $kpi)
                <x-dashboard.kpi-card :kpi="$kpi" />
            @endforeach
        </div>
    @endif

    <div class="sl-dashboard-split">
        {{-- Documents --}}
        <section aria-labelledby="dashboard-documents-title">
            <div class="sl-sec-bar">
                <h2 id="dashboard-documents-title" class="sl-sec-title">
                    Recommandés pour vous
                    @if (! empty($overview['section_subtitle']))
                        <small>{{ $overview['section_subtitle'] }}</small>
                    @endif
                </h2>
                <div class="sl-desktop-only flex gap-1.5" role="group" aria-label="Filtrer par type">
                    @foreach (config('studylib.document_filters', []) as $chip)
                        <button
                            type="button"
                            wire:click="setFilter('{{ $chip['value'] }}')"
                            wire:loading.attr="disabled"
                            @class(['sl-chip', 'is-active' => $filter === $chip['value']])
                            aria-pressed="{{ $filter === $chip['value'] ? 'true' : 'false' }}"
                        >
                            {{ $chip['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            @if (! $ready)
                <div class="sl-doc-list sl-skeleton" aria-hidden="true">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="sl-sk sl-sk-doc"></div>
                    @endfor
                </div>
            @elseif ($documents->isEmpty())
                <x-ui.empty-state
                    title="Aucun document dans ce filtre"
                    description="Aucune ressource ne correspond pour l'instant. Soyez le premier à en partager une avec votre promo."
                >
                    <x-slot:icon>
                        <x-ui.icon name="library" class="h-[30px] w-[30px]" />
                    </x-slot:icon>
                    <x-ui.button variant="primary" href="{{ route('documents.index') }}">
                        <x-ui.icon name="plus" />
                        Déposer un document
                    </x-ui.button>
                </x-ui.empty-state>
            @else
                <div class="sl-doc-list" wire:loading.class="opacity-60">
                    @foreach ($documents as $document)
                        <x-dashboard.document-row :document="$document" />
                    @endforeach
                </div>
            @endif

            <div class="mt-5 flex justify-center">
                <x-ui.button variant="secondary" href="{{ route('documents.index') }}">
                    Voir toute la bibliothèque
                </x-ui.button>
            </div>
        </section>

        {{-- Rail --}}
        <aside class="sl-dashboard-rail" aria-label="Informations complémentaires">
            <x-ui.panel title="Événements proches" action-label="Tout voir" :action-href="route('events.index')">
                @forelse ($events as $event)
                    <x-dashboard.event-item :event="$event" />
                @empty
                    <p class="py-3 text-sm text-muted">Aucun événement à venir.</p>
                @endforelse
            </x-ui.panel>

            <x-ui.panel title="Vidéos recommandées" action-label="YouTube" action-href="#">
                @forelse ($videos as $video)
                    <x-dashboard.video-item :video="$video" />
                @empty
                    <p class="py-3 text-sm text-muted">Aucune vidéo recommandée pour le moment.</p>
                @endforelse
            </x-ui.panel>

            <x-dashboard.ai-panel
                :completion="$overview['profile_completion'] ?? 0"
                :internship-count="$overview['internship_match_label'] ?? '0'"
            />
        </aside>
    </div>
</div>

@if ($detailOpen && $detail)
    <button type="button" class="sl-lib-scrim is-open" wire:click="closeDetail" aria-label="Fermer la fiche projet"></button>
@endif

<aside
    @class(['sl-lib-drawer', 'is-open' => $detailOpen && $detail])
    role="dialog"
    aria-modal="true"
    aria-label="Fiche projet"
>
    @if ($detail)
        @php
            $level = $detail->level;
            $difficulty = $level instanceof \App\Enums\StudyLevel
                ? $ideaService->difficultyDots($level)
                : 2;
        @endphp

        <div class="sl-lib-drawer__head">
            <span class="text-sm font-semibold text-muted">Fiche projet</span>
            <button type="button" class="sl-icon-btn" wire:click="closeDetail" aria-label="Fermer">
                <x-ui.icon name="x" class="h-5 w-5" />
            </button>
        </div>

        <div class="sl-lib-drawer__body">
            <div class="mb-4 flex flex-wrap items-start gap-2">
                @if ($detail->source === \App\Enums\IdeaSource::Ai)
                    <span class="sl-prj-source sl-prj-source--ai">
                        <x-ui.icon name="sparkles" class="h-3 w-3" />
                        Généré par IA
                    </span>
                @else
                    <span class="sl-prj-source sl-prj-source--student">
                        <x-ui.icon name="user" class="h-3 w-3" />
                        Proposé par un étudiant
                    </span>
                @endif
                @if ($level)
                    <x-ui.badge variant="primary">{{ $level->label() }}</x-ui.badge>
                @endif
                @if ($detail->filiere)
                    <x-ui.badge variant="neutral">{{ $detail->filiere->name }}</x-ui.badge>
                @endif
            </div>

            <h2 class="text-h2 leading-tight font-bold tracking-tight">{{ $detail->title }}</h2>

            <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-muted">
                <span class="inline-flex items-center gap-1.5">
                    <span class="sl-prj-diff" aria-label="Difficulté estimée">
                        @for ($dot = 1; $dot <= 3; $dot++)
                            <span @class(['sl-prj-diff__dot', 'is-on' => $dot <= $difficulty])></span>
                        @endfor
                    </span>
                    Difficulté estimée
                </span>
                <span>{{ $ideaService->maskedAuthorName($detail) }}</span>
                <span>{{ $detail->created_at?->translatedFormat('d M Y') }}</span>
            </div>

            <section class="sl-prj-det-section">
                <h3 class="sl-prj-det-section__title">Description</h3>
                <p class="text-sm leading-relaxed text-ink-soft">{{ $detail->description }}</p>
            </section>

            @if ($detail->repo_url)
                <section class="sl-prj-det-section">
                    <h3 class="sl-prj-det-section__title">Dépôt GitHub</h3>
                    <a href="{{ $detail->repo_url }}" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-primary hover:underline">
                        {{ $detail->repo_url }}
                    </a>
                </section>
            @endif

            <section class="sl-prj-det-section">
                <h3 class="sl-prj-det-section__title">Conseil</h3>
                <p class="text-sm text-ink-soft">Documentez votre démarche (README, captures, démo) : un projet bien présenté vaut autant que le code lui-même sur un CV.</p>
            </section>
        </div>

        <div class="sl-lib-drawer__foot">
            <button type="button" wire:click="closeDetail" class="sl-btn sl-btn--secondary">Fermer</button>
            @if ($detail->repo_url)
                <a href="{{ $detail->repo_url }}" target="_blank" rel="noopener noreferrer" class="sl-btn sl-btn--primary">
                    <x-ui.icon name="grid" class="h-4 w-4" />
                    Voir le dépôt
                </a>
            @else
                <button type="button" wire:click="closeDetail" class="sl-btn sl-btn--primary">
                    <x-ui.icon name="check" class="h-4 w-4" />
                    Noté pour mon CV
                </button>
            @endif
        </div>
    @endif
</aside>

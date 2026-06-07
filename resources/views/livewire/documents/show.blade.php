<div>
    @php
        $colors = $document->thumbColors();
        $avgStars = (int) round($document->avg_rating);
        $authorInitials = collect(preg_split('/\s+/', trim($document->author?->name ?? '')) ?: [])
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
        $viewer = auth()->user();
    @endphp

    <div class="sl-doc-layout">
        <div class="sl-doc-main">
            {{-- Header --}}
            <header class="sl-doc-head">
                <div class="sl-doc-head__top">
                    <div
                        class="sl-doc-head__icon"
                        style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }};"
                        aria-hidden="true"
                    >
                        {{ $document->fileKindLabel() }}
                    </div>
                    <div class="min-w-0">
                        <div class="mb-3 flex flex-wrap gap-1.5">
                            <x-ui.badge :variant="$document->type->badgeVariant()">{{ $document->type->label() }}</x-ui.badge>
                            @if ($document->module)
                                <x-ui.badge variant="neutral">{{ $document->module->name }}</x-ui.badge>
                                <x-ui.badge variant="neutral">S{{ $document->module->semester }}</x-ui.badge>
                            @endif
                            @if ($document->status !== \App\Enums\DocumentStatus::Approved)
                                <x-ui.badge variant="warning">En modération</x-ui.badge>
                            @endif
                        </div>
                        <h1 class="sl-doc-head__title">{{ $document->title }}</h1>
                        <dl class="sl-doc-head__meta">
                            <div>
                                <dt>Type</dt>
                                <dd>{{ $document->type->label() }} · {{ $document->fileKindLabel() }}</dd>
                            </div>
                            @if ($document->year_concern)
                                <div>
                                    <dt>Année concernée</dt>
                                    <dd>
                                        <x-ui.icon name="calendar" class="h-3.5 w-3.5" />
                                        {{ $document->year_concern }}-{{ $document->year_concern + 1 }}
                                    </dd>
                                </div>
                            @endif
                            <div>
                                <dt>Ajouté le</dt>
                                <dd>{{ $document->created_at?->translatedFormat('j F Y') }}</dd>
                            </div>
                            <div>
                                <dt>Note moyenne</dt>
                                <dd>
                                    <x-ui.star-rating :value="$avgStars" />
                                    {{ number_format($document->avg_rating, 1, ',', ' ') }}
                                    <span class="font-normal text-muted">({{ $document->ratings_count }})</span>
                                </dd>
                            </div>
                            <div>
                                <dt>Téléchargements</dt>
                                <dd>
                                    <x-ui.icon name="download" class="h-3.5 w-3.5" />
                                    {{ $document->downloads_count }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                @if ($document->author)
                    <div class="sl-doc-head__author">
                        <x-ui.avatar :initials="$authorInitials !== '' ? $authorInitials : '?'" />
                        <div>
                            <div class="text-sm font-semibold">{{ $document->author->name }}</div>
                            <div class="text-xs text-muted">
                                @if ($document->module?->filiere)
                                    {{ $document->module->filiere->name }}
                                @endif
                                · {{ $authorDocumentCount }} document{{ $authorDocumentCount > 1 ? 's' : '' }} partagé{{ $authorDocumentCount > 1 ? 's' : '' }}
                            </div>
                        </div>
                        @if ($document->author->email_verified_at)
                            <x-ui.badge variant="success" class="ml-auto">
                                <x-ui.icon name="check" class="h-3 w-3" />
                                Vérifié
                            </x-ui.badge>
                        @endif
                    </div>
                @endif
            </header>

            {{-- Action bar --}}
            <div class="sl-doc-actionbar">
                <form method="POST" action="{{ route('documents.download', $document) }}" class="sl-doc-actionbar__dl">
                    @csrf
                    <x-ui.button variant="primary" type="submit" class="w-full">
                        <x-ui.icon name="download" />
                        Télécharger le {{ $document->fileKindLabel() }}
                    </x-ui.button>
                </form>
                <button type="button" class="sl-doc-act" wire:click="openRateModal">
                    <x-ui.icon name="star" class="h-[18px] w-[18px]" />
                    <span>{{ $userRatingScore ? 'Modifier ma note' : 'Noter' }}</span>
                </button>
            </div>

            {{-- Preview --}}
            <section
                class="sl-doc-preview"
                aria-label="Aperçu du document"
                x-data="{ zoom: 100 }"
            >
                <div class="sl-doc-preview__toolbar">
                    <span class="sl-doc-preview__title">
                        <x-ui.icon name="file" class="h-4 w-4 text-danger-ink" />
                        Aperçu — {{ $document->fileBasename() }}
                    </span>
                    <div class="flex-1"></div>
                    <div class="sl-doc-preview__ctrl">
                        <button type="button" class="sl-doc-preview__btn" x-on:click="zoom = Math.max(60, zoom - 20)" aria-label="Dézoomer">
                            <svg class="h-[15px] w-[15px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/></svg>
                        </button>
                        <span class="sl-doc-preview__ind" x-text="zoom + '%'">100%</span>
                        <button type="button" class="sl-doc-preview__btn" x-on:click="zoom = Math.min(160, zoom + 20)" aria-label="Zoomer">
                            <svg class="h-[15px] w-[15px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                        </button>
                    </div>
                </div>
                <div class="sl-doc-preview__stage">
                    <div class="sl-doc-preview__page" x-bind:style="'transform: scale(' + (zoom / 100) + ')'">
                        <div class="sl-doc-preview__page-title">{{ $document->title }}</div>
                        @if ($document->module && $document->author)
                            <div class="sl-doc-preview__page-sub">
                                {{ $document->module->name }} · {{ $document->author->name }}
                            </div>
                        @endif
                        <div class="sl-doc-preview__line sl-doc-preview__line--h"></div>
                        <div class="sl-doc-preview__line sl-doc-preview__line--m"></div>
                        <div class="sl-doc-preview__line"></div>
                        <div class="sl-doc-preview__line sl-doc-preview__line--s"></div>
                        <div class="sl-doc-preview__formula"></div>
                        <div class="sl-doc-preview__line"></div>
                        <div class="sl-doc-preview__line sl-doc-preview__line--m"></div>
                        <div class="sl-doc-preview__line sl-doc-preview__line--s"></div>
                        @if ($document->description)
                            <p class="mt-4 text-[10px] leading-relaxed text-muted">{{ \Illuminate\Support\Str::limit($document->description, 200) }}</p>
                        @endif
                        <div class="sl-doc-preview__pagenum">— 1 —</div>
                    </div>
                </div>
            </section>

            {{-- Comments (empty state — backend à venir) --}}
            <section class="sl-doc-section" aria-labelledby="doc-comments-heading">
                <div class="sl-doc-section__head">
                    <h2 id="doc-comments-heading" class="text-h3 font-semibold">
                        Commentaires <span class="font-normal text-muted">(0)</span>
                    </h2>
                    <span class="flex items-center gap-1.5 text-xs text-muted">
                        <x-ui.icon name="shield" class="h-3.5 w-3.5 text-success" />
                        Modération communautaire active
                    </span>
                </div>
                <x-ui.empty-state
                    flaticon="empty"
                    title="Aucun commentaire"
                    description="Soyez le premier à partager un retour utile sur ce document."
                    class="!py-8"
                >
                </x-ui.empty-state>
            </section>

            {{-- Version --}}
            <section class="sl-doc-section" aria-labelledby="doc-version-heading">
                <h2 id="doc-version-heading" class="text-h3 font-semibold">Historique des versions</h2>
                <p class="mt-2 text-sm text-muted">Les mises à jour apportées par l'auteur ou la communauté.</p>
                <div class="mt-5">
                    <div class="sl-doc-version">
                        <div class="sl-doc-version__dot sl-doc-version__dot--current">v1</div>
                        <div class="flex-1">
                            <div class="text-sm font-semibold">
                                Version actuelle
                                <x-ui.badge variant="success">Actuelle</x-ui.badge>
                            </div>
                            <p class="mt-0.5 text-xs text-muted">
                                Dépôt initial
                                @if ($document->author)
                                    · {{ $document->author->name }}
                                @endif
                                · {{ $document->created_at?->translatedFormat('j F Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- Right rail --}}
        <aside class="sl-doc-rail" aria-label="Contenus associés">
            @if ($viewer?->filiere && $document->module?->filiere_id === $viewer->filiere_id)
                <div class="sl-doc-rec">
                    <div class="sl-doc-rec__ico">
                        <x-ui.icon name="star" class="h-5 w-5 text-white" />
                    </div>
                    <h3 class="text-h4 font-bold">Recommandé pour votre niveau</h3>
                    <p class="mt-1 text-sm opacity-90">
                        Pertinent pour {{ $viewer->filiere->name }}
                        @if ($viewer->year_level)
                            L{{ $viewer->year_level }}
                        @endif
                        @if ($document->module)
                            — Semestre {{ $document->module->semester }}
                        @endif
                        , votre filière actuelle.
                    </p>
                </div>
            @endif

            @if ($similarDocuments->isNotEmpty())
                <div class="sl-doc-panel">
                    <div class="sl-doc-panel__head">
                        <h3 class="text-h4 font-semibold">Documents similaires</h3>
                        <a href="{{ route('documents.index', ['module' => $document->module_id]) }}" wire:navigate class="text-xs font-semibold text-primary">Tout voir</a>
                    </div>
                    <div class="sl-doc-panel__body">
                        @foreach ($similarDocuments as $similar)
                            <x-documents.mini-link :document="$similar" wire:key="similar-{{ $similar->id }}" />
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($examDocuments->isNotEmpty())
                <div class="sl-doc-panel">
                    <div class="sl-doc-panel__head">
                        <h3 class="text-h4 font-semibold">Examens — même module</h3>
                        <a href="{{ route('documents.index', ['module' => $document->module_id, 'types' => ['examen']]) }}" wire:navigate class="text-xs font-semibold text-primary">Tout voir</a>
                    </div>
                    <div class="sl-doc-panel__body">
                        @foreach ($examDocuments as $exam)
                            <a href="{{ route('documents.show', $exam) }}" wire:navigate class="sl-doc-mini" wire:key="exam-{{ $exam->id }}">
                                <div class="sl-doc-mini__ico sl-doc-mini__ico--exam" aria-hidden="true">{{ $exam->fileKindLabel() }}</div>
                                <div class="sl-doc-mini__info">
                                    <h4>{{ $exam->title }}</h4>
                                    <p>{{ $exam->module?->name }} · {{ $exam->downloads_count }} téléch.</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>

    {{-- Rate modal --}}
    @if ($rateModalOpen)
        <button type="button" class="sl-doc-scrim" wire:click="closeRateModal" aria-label="Fermer"></button>
    @endif
    <div @class(['sl-doc-modal-wrap', 'is-open' => $rateModalOpen]) role="dialog" aria-modal="true" aria-label="Noter ce document">
        <div class="sl-doc-modal">
            <div class="sl-doc-modal__head">
                <h3 class="text-h3 font-semibold">Noter ce document</h3>
                <p class="mt-1.5 text-sm text-ink-soft">Votre avis aide la promo à repérer les meilleures ressources.</p>
            </div>
            <div class="sl-doc-modal__body">
                <div class="sl-doc-rate-stars" role="group" aria-label="Choisir une note">
                    @for ($star = 1; $star <= 5; $star++)
                        <button
                            type="button"
                            wire:click="setRateScore({{ $star }})"
                            aria-label="{{ $star }} sur 5"
                        >
                            <svg @class(['h-[38px] w-[38px]', 'text-star' => $rateScore >= $star, 'text-border-strong' => $rateScore < $star]) viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2.5l2.9 5.9 6.5.95-4.7 4.58 1.1 6.47L12 17.4l-5.8 3.05 1.1-6.47L2.6 9.35l6.5-.95z"/>
                            </svg>
                        </button>
                    @endfor
                </div>
                <p class="text-center text-sm font-semibold text-muted">
                    @if ($rateScore > 0)
                        {{ match ($rateScore) {
                            1 => 'Décevant',
                            2 => 'Moyen',
                            3 => 'Correct',
                            4 => 'Très bien',
                            default => 'Excellent',
                        } }} · {{ $rateScore }}/5
                    @else
                        Sélectionnez une note
                    @endif
                </p>
            </div>
            <div class="sl-doc-modal__foot">
                <button type="button" class="sl-btn sl-btn--secondary" wire:click="closeRateModal">Annuler</button>
                <button
                    type="button"
                    class="sl-btn sl-btn--primary"
                    wire:click="submitRating"
                    wire:loading.attr="disabled"
                    @if ($rateScore < 1) disabled @endif
                >
                    <span wire:loading.remove wire:target="submitRating">Envoyer ma note</span>
                    <span wire:loading wire:target="submitRating">Envoi…</span>
                </button>
            </div>
        </div>
    </div>
</div>

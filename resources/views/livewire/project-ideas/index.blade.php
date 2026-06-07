<div>
    @if (session('success'))
        <div class="mb-4 rounded-md border border-success/30 bg-success-soft px-4 py-3 text-sm font-semibold text-success-ink" role="status">
            {{ session('success') }}
        </div>
    @endif

    <section class="sl-prj-hero" aria-label="Introduction projets CV">
        <div class="sl-prj-hero__ico" aria-hidden="true">
            <x-ui.icon name="layers" class="h-[26px] w-[26px]" />
        </div>
        <div class="min-w-0 flex-1">
            <h2 class="text-h3 leading-tight font-bold tracking-tight">Trouvez un projet pertinent pour votre filière</h2>
            <p class="mt-1 text-sm text-muted">Des idées concrètes pour étoiler votre CV — proposées par la promo ou générées pour votre profil.</p>
        </div>
        <div class="hidden flex-1 lg:block"></div>
        <div class="flex w-full flex-wrap gap-2 sm:w-auto">
            <a href="#ai-module" class="sl-btn sl-btn--secondary">
                <x-ui.icon name="sparkles" class="h-4 w-4" />
                Idées IA
            </a>
            <button type="button" wire:click="openPropose" class="sl-btn sl-btn--primary">
                <x-ui.icon name="plus" class="h-4 w-4" />
                Proposer un projet
            </button>
        </div>
    </section>

    <div class="sl-lib-layout">
        @if ($filtersOpen)
            <button type="button" class="sl-lib-scrim is-open" wire:click="$set('filtersOpen', false)" aria-label="Fermer les filtres"></button>
        @endif

        <aside @class(['sl-lib-filters', 'is-open' => $filtersOpen]) aria-label="Filtres projets">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-h4 font-semibold">Filtres</h2>
                <button type="button" wire:click="resetFilters" class="text-xs font-semibold text-primary">Réinitialiser</button>
            </div>

            <div class="sl-lib-fgroup">
                <label class="sl-lib-flabel" for="prj-search">Recherche</label>
                <input
                    id="prj-search"
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Titre, mot-clé…"
                    class="sl-lib-fselect"
                />
            </div>

            <div class="sl-lib-fgroup">
                <label class="sl-lib-flabel" for="prj-filiere">Filière</label>
                <select id="prj-filiere" wire:model.live="filiereId" class="sl-lib-fselect">
                    <option value="">Toutes</option>
                    @foreach ($filieres as $filiere)
                        <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sl-lib-fgroup">
                <label class="sl-lib-flabel" for="prj-niveau">Niveau</label>
                <select id="prj-niveau" wire:model.live="level" class="sl-lib-fselect">
                    <option value="">Tous</option>
                    @foreach ($studyLevels as $studyLevel)
                        <option value="{{ $studyLevel->value }}">{{ $studyLevel->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sl-lib-fgroup">
                <span class="sl-lib-flabel">Source</span>
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($ideaSources as $ideaSource)
                        <button
                            type="button"
                            wire:click="setSourceFilter('{{ $ideaSource->value }}')"
                            @class(['sl-prj-fchip', 'is-on' => $source === $ideaSource->value])
                        >
                            {{ $ideaSource->label() }}
                        </button>
                    @endforeach
                </div>
            </div>

            <button type="button" wire:click="$set('filtersOpen', false)" class="sl-btn sl-btn--primary sl-lib-mobile-filter mt-4 w-full lg:hidden">
                Voir les résultats
            </button>
        </aside>

        <section>
            <div class="sl-lib-results-bar">
                <button type="button" wire:click="$set('filtersOpen', true)" class="sl-btn sl-btn--secondary sl-lib-mobile-filter">
                    <x-ui.icon name="filter" class="h-4 w-4" />
                    Filtres
                </button>
                <p class="text-sm text-muted">
                    <strong class="text-ink">{{ $ideas->total() }}</strong>
                    projet{{ $ideas->total() > 1 ? 's' : '' }}
                </p>
                <div class="flex-1"></div>
                <div class="sl-lib-sort">
                    <select wire:model.live="sort" aria-label="Tri des projets">
                        <option value="recent">Tri : Plus récents</option>
                        <option value="level">Tri : Niveau croissant</option>
                    </select>
                </div>
            </div>

            <div wire:loading.class="opacity-60">
                @if ($ideas->isEmpty())
                    <x-ui.empty-state
                        title="Aucun projet trouvé"
                        description="Aucune idée ne correspond à vos critères. Élargissez la recherche, proposez la vôtre ou générez des idées IA."
                    >
                        <x-slot:icon>
                            <x-ui.icon name="layers" class="h-[30px] w-[30px]" />
                        </x-slot:icon>
                        <div class="flex flex-wrap justify-center gap-3">
                            <button type="button" wire:click="resetFilters" class="sl-btn sl-btn--secondary">Réinitialiser</button>
                            <button type="button" wire:click="openPropose" class="sl-btn sl-btn--primary">Proposer un projet</button>
                        </div>
                    </x-ui.empty-state>
                @else
                    <div class="sl-prj-grid">
                        @foreach ($ideas as $idea)
                            <x-projects.idea-card :idea="$idea" :idea-service="$ideaService" />
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($ideas->hasPages())
                <div class="mt-6">
                    {{ $ideas->links('components.ui.pagination') }}
                </div>
            @endif

            <section id="ai-module" class="sl-prj-ai" aria-label="Génération IA">
                <div class="sl-prj-ai__head">
                    <div class="sl-prj-ai__badge" aria-hidden="true">
                        <x-ui.icon name="sparkles" class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="text-h4 font-semibold">
                            Générer 3 idées pour mon profil
                            <x-ui.badge variant="primary" class="ml-1">IA</x-ui.badge>
                        </h3>
                        <p class="mt-1 text-sm text-muted">Un coup de pouce quand la bibliothèque ne suffit pas. Décrivez votre profil, l'IA propose des pistes.</p>
                    </div>
                </div>

                <form wire:submit="generateAiIdeas" class="sl-prj-ai__body">
                    <div class="sl-prj-ai__form">
                        <x-ui.field label="Filière" for="ai-filiere" :error="$errors->first('aiFiliereId')">
                            <select id="ai-filiere" wire:model="aiFiliereId" class="sl-input w-full">
                                @foreach ($filieres as $filiere)
                                    <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>

                        <x-ui.field label="Niveau" for="ai-niveau" :error="$errors->first('aiLevel')">
                            <select id="ai-niveau" wire:model="aiLevel" class="sl-input w-full">
                                @foreach ($studyLevels as $studyLevel)
                                    <option value="{{ $studyLevel->value }}">{{ $studyLevel->label() }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>

                        <x-ui.field label="Centres d'intérêt" for="ai-interets" class="sm:col-span-2" :error="$errors->first('aiInterests')">
                            <input
                                id="ai-interets"
                                type="text"
                                wire:model="aiInterests"
                                class="sl-input w-full"
                                placeholder="ex. développement web, intelligence artificielle, jeux vidéo…"
                            />
                        </x-ui.field>

                        <button type="submit" class="sl-btn sl-btn--primary sl-prj-ai__gen" wire:loading.attr="disabled" wire:target="generateAiIdeas">
                            <x-ui.icon name="sparkles" class="h-4 w-4" />
                            <span wire:loading.remove wire:target="generateAiIdeas">Générer</span>
                            <span wire:loading wire:target="generateAiIdeas">Génération…</span>
                        </button>
                    </div>

                    <div class="sl-prj-ai__results">
                        @if ($aiIdeas->isEmpty() && ! $aiLoading)
                            <div class="sl-prj-ai__empty">
                                <x-ui.icon name="info" class="mx-auto mb-3 h-8 w-8 text-primary" />
                                Renseignez votre profil puis cliquez sur « Générer » — 3 idées de projets apparaîtront ici.
                            </div>
                        @else
                            @foreach ($aiIdeas as $aiIdea)
                                <x-projects.idea-card :idea="$aiIdea" :idea-service="$ideaService" wire:key="ai-{{ $aiIdea->id }}" />
                            @endforeach
                        @endif
                    </div>
                </form>
            </section>
        </section>
    </div>

    @include('partials.project-ideas.detail-drawer')
    @include('partials.project-ideas.propose-drawer')
</div>

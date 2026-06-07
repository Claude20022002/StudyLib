<div>
    {{-- Search hero --}}
    <section class="sl-lib-search" aria-label="Recherche">
        <div class="sl-lib-search__head">
            <x-ui.flaticon name="library" class="sl-flaticon--hero" />
            <p class="sl-lib-search__lead">
                Trouvez n'importe quel document en moins de 10 secondes
            </p>
        </div>
        <div class="sl-lib-search__box">
            <x-ui.icon name="search" class="sl-lib-search__icon" />
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher par titre, module, mot-clé, auteur…"
                class="sl-lib-search__input"
                aria-label="Rechercher dans la bibliothèque"
            />
            @if ($search !== '')
                <button type="button" wire:click="clearSearch" class="sl-lib-search__clear" aria-label="Effacer la recherche">
                    <x-ui.icon name="x" class="h-4 w-4" />
                </button>
            @endif
            <x-ui.button variant="primary" size="sm" class="sl-lib-search__go" wire:click="$refresh">
                <x-ui.icon name="search" />
                <span class="hidden sm:inline">Rechercher</span>
            </x-ui.button>
        </div>
        <div class="sl-lib-quick-tags">
            <span class="text-xs font-semibold text-muted">Suggestions :</span>
            @foreach (['bases de données', 'algorithmique', 'réseaux', 'annales'] as $suggestion)
                <button type="button" wire:click="applyQuickSearch('{{ $suggestion }}')" class="sl-lib-qtag">
                    {{ ucfirst($suggestion) }}
                </button>
            @endforeach
        </div>
    </section>

    <div class="sl-lib-layout">
        {{-- Mobile filter scrim --}}
        @if ($filtersOpen)
            <button type="button" class="sl-lib-scrim" wire:click="$set('filtersOpen', false)" aria-label="Fermer les filtres"></button>
        @endif

        {{-- Filters --}}
        <aside @class(['sl-lib-filters', 'is-open' => $filtersOpen]) aria-label="Filtres">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-h4 font-semibold">Filtres</h2>
                <button type="button" wire:click="resetFilters" class="text-xs font-semibold text-primary">Réinitialiser</button>
            </div>

            <div class="sl-lib-fgroup">
                <label class="sl-lib-flabel" for="lib-filiere">Filière</label>
                <select id="lib-filiere" wire:model.live="filiereId" class="sl-lib-fselect">
                    <option value="">Toutes les filières</option>
                    @foreach ($filieres as $filiere)
                        <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sl-lib-fgroup">
                <label class="sl-lib-flabel" for="lib-semestre">Semestre</label>
                <select id="lib-semestre" wire:model.live="semester" class="sl-lib-fselect">
                    <option value="">Tous les semestres</option>
                    @for ($sem = 1; $sem <= 6; $sem++)
                        <option value="{{ $sem }}">S{{ $sem }}</option>
                    @endfor
                </select>
            </div>

            <div class="sl-lib-fgroup">
                <label class="sl-lib-flabel" for="lib-module">Module</label>
                <select id="lib-module" wire:model.live="moduleId" class="sl-lib-fselect">
                    <option value="">Tous les modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module->id }}">{{ $module->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sl-lib-fgroup">
                <label class="sl-lib-flabel" for="lib-annee">Année concernée</label>
                <select id="lib-annee" wire:model.live="yearConcern" class="sl-lib-fselect">
                    <option value="">Toutes les années</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year['value'] }}">{{ $year['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sl-lib-fgroup">
                <span class="sl-lib-flabel">Type de document</span>
                @foreach (\App\Enums\DocumentType::cases() as $type)
                    <label class="sl-lib-fcheck">
                        <input
                            type="checkbox"
                            wire:click="toggleType('{{ $type->value }}')"
                            @if (in_array($type->value, $types, true)) checked @endif
                        />
                        <span class="sl-lib-fbox">
                            <x-ui.icon name="check" class="h-3 w-3 text-white" />
                        </span>
                        {{ $type->label() }}
                        <span class="ml-auto text-xs text-muted">{{ $typeCounts[$type->value] ?? 0 }}</span>
                    </label>
                @endforeach
            </div>

            <div class="sl-lib-fgroup">
                <span class="sl-lib-flabel">Note minimale</span>
                <div class="flex gap-1">
                    @for ($star = 1; $star <= 5; $star++)
                        <button
                            type="button"
                            wire:click="setMinRating({{ $star }})"
                            @class(['sl-lib-rf-star', 'is-on' => $minRating >= $star])
                            aria-label="{{ $star }} étoiles minimum"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 5.9 6.5.95-4.7 4.58 1.1 6.47L12 17.4l-5.8 3.05 1.1-6.47L2.6 9.35l6.5-.95z"/></svg>
                        </button>
                    @endfor
                </div>
                <p class="mt-2 text-xs text-muted">
                    {{ $minRating > 0 ? $minRating.' étoiles et plus' : 'Toutes les notes' }}
                </p>
            </div>

            <x-ui.button variant="secondary" size="sm" class="mt-4 w-full sl-mobile-only" wire:click="$set('filtersOpen', false)">
                Voir les résultats
            </x-ui.button>
        </aside>

        {{-- Results --}}
        <section aria-label="Résultats">
            <div class="sl-lib-results-bar">
                <button type="button" class="sl-lib-mobile-filter sl-mobile-only" wire:click="$set('filtersOpen', true)">
                    <x-ui.icon name="grid" class="h-4 w-4" />
                    Filtres
                </button>

                <p class="text-sm text-ink-soft">
                    <strong class="text-ink">{{ $documents->total() }}</strong>
                    document{{ $documents->total() > 1 ? 's' : '' }} trouvé{{ $documents->total() > 1 ? 's' : '' }}
                </p>

                <div class="flex-1"></div>

                <select wire:model.live="sort" class="sl-lib-sort" aria-label="Trier les résultats">
                    <option value="recent">Tri : Plus récents</option>
                    <option value="popular">Tri : Plus populaires</option>
                    <option value="pertinence">Tri : Pertinence</option>
                </select>

                <div class="sl-lib-view-toggle" role="group" aria-label="Mode d'affichage">
                    <button type="button" wire:click="setViewMode('list')" @class(['sl-lib-vt-btn', 'is-active' => $viewMode === 'list']) aria-label="Vue liste">
                        <svg class="h-[17px] w-[17px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                    </button>
                    <button type="button" wire:click="setViewMode('grid')" @class(['sl-lib-vt-btn', 'is-active' => $viewMode === 'grid']) aria-label="Vue grille">
                        <x-ui.icon name="grid" class="h-[17px] w-[17px]" />
                    </button>
                </div>

                <x-ui.button variant="primary" size="sm" class="sl-desktop-only" wire:click="openUpload">
                    <x-ui.icon name="plus" />
                    Déposer
                </x-ui.button>
            </div>

            @if ($activeChips !== [])
                <div class="mb-4 flex flex-wrap gap-2">
                    @foreach ($activeChips as $chip)
                        <span class="sl-lib-af-chip">
                            {{ $chip['label'] }}
                            @if (($chip['key'] ?? '') === 'type' && isset($chip['value']))
                                <button type="button" wire:click="removeFilter('type', '{{ $chip['value'] }}')" aria-label="Retirer le filtre">
                                    <x-ui.icon name="x" class="h-2.5 w-2.5" />
                                </button>
                            @else
                                <button type="button" wire:click="removeFilter('{{ $chip['key'] }}')" aria-label="Retirer le filtre">
                                    <x-ui.icon name="x" class="h-2.5 w-2.5" />
                                </button>
                            @endif
                        </span>
                    @endforeach
                </div>
            @endif

            <div wire:loading.class="opacity-60">
                @if ($documents->isEmpty())
                    <x-ui.empty-state
                        flaticon="search"
                        title="Aucun résultat"
                        description="Aucun document ne correspond à votre recherche ou à vos filtres. Essayez d'élargir les critères, ou partagez le vôtre."
                    >
                        <div class="flex flex-wrap justify-center gap-3">
                            <x-ui.button variant="secondary" wire:click="resetFilters">Réinitialiser les filtres</x-ui.button>
                            <x-ui.button variant="primary" wire:click="openUpload">
                                <x-ui.icon name="plus" />
                                Déposer un document
                            </x-ui.button>
                        </div>
                    </x-ui.empty-state>
                @elseif ($viewMode === 'grid')
                    <div class="sl-lib-grid">
                        @foreach ($documents as $document)
                            <x-documents.grid-card :document="$document" wire:key="doc-card-{{ $document->id }}" />
                        @endforeach
                    </div>
                @else
                    <div class="sl-lib-list">
                        @foreach ($documents as $document)
                            <x-documents.list-row :document="$document" wire:key="doc-row-{{ $document->id }}" />
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($documents->hasPages())
                <div class="mt-6">
                    {{ $documents->links('components.ui.pagination') }}
                </div>
            @endif
        </section>
    </div>

    @include('partials.documents.upload-drawer')
</div>

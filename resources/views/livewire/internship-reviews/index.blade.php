<div>
    @if (session('success'))
        <x-ui.page-alert>{{ session('success') }}</x-ui.page-alert>
    @endif

    {{-- Search hero --}}
    <section class="sl-stg-hero" aria-label="Recherche stages">
        <div class="sl-stg-hero__search">
            <x-ui.icon name="search" class="sl-stg-hero__icon" />
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher une entreprise, une ville, un secteur…"
                class="sl-stg-hero__input"
                aria-label="Rechercher un stage"
            />
            @if ($search !== '')
                <button type="button" wire:click="clearSearch" class="sl-stg-hero__clear" aria-label="Effacer la recherche">
                    <x-ui.icon name="x" class="h-4 w-4" />
                </button>
            @endif
        </div>
        <button type="button" wire:click="openShare()" class="sl-btn sl-btn--primary sl-stg-hero__cta">
            <x-ui.icon name="upload" class="h-4 w-4" />
            Partager mon retour de stage
        </button>
    </section>

    <div class="sl-lib-layout">
        @if ($filtersOpen)
            <button type="button" class="sl-lib-scrim is-open" wire:click="$set('filtersOpen', false)" aria-label="Fermer les filtres"></button>
        @endif

        <aside @class(['sl-lib-filters', 'is-open' => $filtersOpen]) aria-label="Filtres stages">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-h4 font-semibold">Filtres</h2>
                <button type="button" wire:click="resetFilters" class="text-xs font-semibold text-primary">Réinitialiser</button>
            </div>

            <div class="sl-lib-fgroup">
                <label class="sl-lib-flabel" for="stg-filiere">Filière</label>
                <select id="stg-filiere" wire:model.live="filiereId" class="sl-lib-fselect">
                    <option value="">Toutes</option>
                    @foreach ($filieres as $filiere)
                        <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sl-lib-fgroup">
                <label class="sl-lib-flabel" for="stg-niveau">Niveau</label>
                <select id="stg-niveau" wire:model.live="yearLevel" class="sl-lib-fselect">
                    <option value="">Tous</option>
                    @for ($level = 1; $level <= 5; $level++)
                        <option value="{{ $level }}">L{{ $level }}</option>
                    @endfor
                </select>
            </div>

            <div class="sl-lib-fgroup">
                <label class="sl-lib-flabel" for="stg-ville">Ville</label>
                <select id="stg-ville" wire:model.live="city" class="sl-lib-fselect">
                    <option value="">Toutes</option>
                    @foreach ($filterOptions['cities'] as $cityOption)
                        <option value="{{ $cityOption }}">{{ $cityOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sl-lib-fgroup">
                <label class="sl-lib-flabel" for="stg-secteur">Secteur</label>
                <select id="stg-secteur" wire:model.live="sector" class="sl-lib-fselect">
                    <option value="">Tous</option>
                    @foreach ($filterOptions['sectors'] as $sectorOption)
                        <option value="{{ $sectorOption }}">{{ $sectorOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sl-lib-fgroup">
                <label class="sl-lib-flabel" for="stg-annee">Année du stage</label>
                <select id="stg-annee" wire:model.live="yearDone" class="sl-lib-fselect">
                    <option value="">Toutes</option>
                    @foreach ($filterOptions['years'] as $yearOption)
                        <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sl-lib-fgroup">
                <span class="sl-lib-flabel">Note minimale</span>
                <div class="flex gap-1">
                    @for ($star = 1; $star <= 5; $star++)
                        <button
                            type="button"
                            wire:click="setMinRating({{ $star }})"
                            @class(['sl-lib-rf-star', 'is-on' => $minRating >= $star])
                            aria-label="{{ $star }} étoile{{ $star > 1 ? 's' : '' }} minimum"
                        >
                            <x-ui.icon name="star" class="h-4 w-4" />
                        </button>
                    @endfor
                </div>
                <p class="mt-2 text-xs text-muted">
                    {{ $minRating > 0 ? $minRating.'★ et plus' : 'Toutes les notes' }}
                </p>
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
                    <strong class="text-ink">{{ $companies->total() }}</strong>
                    entreprise{{ $companies->total() > 1 ? 's' : '' }}
                </p>
                <div class="flex-1"></div>
                <div class="sl-lib-sort">
                    <select wire:model.live="sort" aria-label="Tri des résultats">
                        <option value="rating">Tri : Mieux notées</option>
                        <option value="reviews">Tri : Plus d'avis</option>
                        <option value="recent">Tri : Plus récentes</option>
                    </select>
                </div>
            </div>

            <div wire:loading.class="opacity-60">
                @if ($companies->isEmpty())
                    <x-ui.empty-state
                        title="Aucune entreprise trouvée"
                        description="Aucun retour ne correspond à vos critères. Élargissez la recherche ou partagez le vôtre."
                    >
                        <x-slot:icon>
                            <x-ui.icon name="search" class="h-[30px] w-[30px]" />
                        </x-slot:icon>
                        <div class="flex flex-wrap justify-center gap-3">
                            <button type="button" wire:click="resetFilters" class="sl-btn sl-btn--secondary">Réinitialiser les filtres</button>
                            <button type="button" wire:click="openShare()" class="sl-btn sl-btn--primary">
                                <x-ui.icon name="plus" class="h-4 w-4" />
                                Partager mon retour
                            </button>
                        </div>
                    </x-ui.empty-state>
                @else
                    <div class="sl-stg-grid">
                        @foreach ($companies as $company)
                            <x-internships.company-card :company="$company" :review-service="$reviewService" />
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($companies->hasPages())
                <div class="mt-6">
                    {{ $companies->links('components.ui.pagination') }}
                </div>
            @endif
        </section>
    </div>

    @include('partials.internship-reviews.detail-drawer')
    @include('partials.internship-reviews.share-drawer')
</div>

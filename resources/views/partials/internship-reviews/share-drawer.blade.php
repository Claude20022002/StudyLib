@if ($shareOpen)
    <button type="button" class="sl-lib-scrim is-open" wire:click="closeShare" aria-label="Fermer le formulaire"></button>
@endif

<aside
    @class(['sl-lib-drawer', 'is-open' => $shareOpen])
    role="dialog"
    aria-modal="true"
    aria-label="Partager mon retour de stage"
>
    <div class="sl-lib-drawer__head">
        <div>
            <h2 class="text-h3 font-semibold">Partager mon retour de stage</h2>
            <p class="text-sm text-muted">Votre expérience aide la promo à mieux choisir.</p>
        </div>
        <button type="button" class="sl-icon-btn" wire:click="closeShare" aria-label="Fermer">
            <x-ui.icon name="x" class="h-5 w-5" />
        </button>
    </div>

    <form wire:submit="submitShare" class="flex min-h-0 flex-1 flex-col">
        <div class="sl-lib-drawer__body">
            <x-ui.field label="Entreprise" for="share-company" :error="$errors->first('shareCompanyName')">
                <input
                    id="share-company"
                    type="text"
                    wire:model="shareCompanyName"
                    class="sl-input w-full @error('shareCompanyName') is-error @enderror"
                    placeholder="Nom de l'entreprise"
                />
            </x-ui.field>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="Ville" for="share-city" :error="$errors->first('shareCompanyCity')">
                    <input
                        id="share-city"
                        type="text"
                        wire:model="shareCompanyCity"
                        class="sl-input w-full"
                        placeholder="Ville"
                        list="share-cities"
                    />
                    <datalist id="share-cities">
                        @foreach ($filterOptions['cities'] as $cityOption)
                            <option value="{{ $cityOption }}"></option>
                        @endforeach
                    </datalist>
                </x-ui.field>

                <x-ui.field label="Secteur" for="share-sector" :error="$errors->first('shareCompanySector')">
                    <input
                        id="share-sector"
                        type="text"
                        wire:model="shareCompanySector"
                        class="sl-input w-full"
                        placeholder="Secteur d'activité"
                        list="share-sectors"
                    />
                    <datalist id="share-sectors">
                        @foreach ($filterOptions['sectors'] as $sectorOption)
                            <option value="{{ $sectorOption }}"></option>
                        @endforeach
                    </datalist>
                </x-ui.field>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="Filière" for="share-filiere" :error="$errors->first('shareFiliereId')">
                    <select id="share-filiere" wire:model="shareFiliereId" class="sl-input w-full">
                        <option value="">Filière…</option>
                        @foreach ($filieres as $filiere)
                            <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                        @endforeach
                    </select>
                </x-ui.field>

                <x-ui.field label="Poste occupé" for="share-position" :error="$errors->first('sharePosition')">
                    <input
                        id="share-position"
                        type="text"
                        wire:model="sharePosition"
                        class="sl-input w-full"
                        placeholder="ex. Stagiaire développeur"
                    />
                </x-ui.field>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="Niveau au stage" for="share-niveau" :error="$errors->first('shareYearLevel')">
                    <select id="share-niveau" wire:model="shareYearLevel" class="sl-input w-full">
                        <option value="">Niveau…</option>
                        @for ($level = 1; $level <= 5; $level++)
                            <option value="{{ $level }}">L{{ $level }}</option>
                        @endfor
                    </select>
                </x-ui.field>

                <x-ui.field label="Année du stage" for="share-annee" :error="$errors->first('shareYearDone')">
                    <select id="share-annee" wire:model="shareYearDone" class="sl-input w-full">
                        <option value="">Année…</option>
                        @foreach ($filterOptions['years'] as $yearOption)
                            <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
            </div>

            <div class="mb-5">
                <span class="mb-2 block text-sm font-semibold text-ink-soft">Notez votre expérience (1–5)</span>
                <div class="flex gap-1">
                    @for ($star = 1; $star <= 5; $star++)
                        <button
                            type="button"
                            wire:click="setShareRating({{ $star }})"
                            @class(['sl-stg-star-btn', 'is-on' => $shareRating >= $star])
                            aria-label="{{ $star }} étoile{{ $star > 1 ? 's' : '' }}"
                        >
                            <x-ui.icon name="star" class="h-6 w-6" />
                        </button>
                    @endfor
                </div>
                @error('shareRating')
                    <p class="mt-2 text-sm text-danger" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <x-ui.field label="Votre retour" for="share-description" :error="$errors->first('shareDescription')">
                <textarea
                    id="share-description"
                    wire:model="shareDescription"
                    class="sl-input min-h-[120px] w-full resize-y py-3"
                    placeholder="Décrivez vos missions, l'ambiance, l'encadrement et vos conseils pour les futurs stagiaires…"
                ></textarea>
            </x-ui.field>

            <label class="sl-lib-fcheck sl-stg-paid cursor-pointer">
                <input type="checkbox" wire:model="shareIsPaid" class="sr-only" />
                <span class="sl-lib-fbox">
                    <x-ui.icon name="check" class="h-3.5 w-3.5" />
                </span>
                <span class="text-sm text-ink-soft"><strong class="text-ink">Stage rémunéré</strong> — cochez si vous avez perçu une gratification.</span>
            </label>
        </div>

        <div class="sl-lib-drawer__foot">
            <button type="button" wire:click="closeShare" class="sl-btn sl-btn--secondary">Annuler</button>
            <button type="submit" class="sl-btn sl-btn--primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="submitShare">Publier mon retour</span>
                <span wire:loading wire:target="submitShare">Publication…</span>
            </button>
        </div>
    </form>
</aside>

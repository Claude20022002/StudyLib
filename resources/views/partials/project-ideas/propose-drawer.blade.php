@if ($proposeOpen)
    <button type="button" class="sl-lib-scrim is-open" wire:click="closePropose" aria-label="Fermer le formulaire"></button>
@endif

<aside
    @class(['sl-lib-drawer', 'is-open' => $proposeOpen])
    role="dialog"
    aria-modal="true"
    aria-label="Proposer un projet"
>
    <div class="sl-lib-drawer__head">
        <div>
            <h2 class="text-h3 font-semibold">Proposer un projet</h2>
            <p class="text-sm text-muted">Partagez une idée concrète pour inspirer la promo.</p>
        </div>
        <button type="button" class="sl-icon-btn" wire:click="closePropose" aria-label="Fermer">
            <x-ui.icon name="x" class="h-5 w-5" />
        </button>
    </div>

    <form wire:submit="submitPropose" class="flex min-h-0 flex-1 flex-col">
        <div class="sl-lib-drawer__body">
            <x-ui.field label="Titre du projet" for="propose-title" :error="$errors->first('proposeTitle')">
                <input
                    id="propose-title"
                    type="text"
                    wire:model="proposeTitle"
                    class="sl-input w-full @error('proposeTitle') is-error @enderror"
                    placeholder="ex. Plateforme d'entraide entre étudiants"
                />
            </x-ui.field>

            <x-ui.field label="Description" for="propose-description" :error="$errors->first('proposeDescription')">
                <textarea
                    id="propose-description"
                    wire:model="proposeDescription"
                    class="sl-input min-h-[140px] w-full resize-y py-3"
                    placeholder="Objectif, missions clés, technologies suggérées, livrables attendus…"
                ></textarea>
            </x-ui.field>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="Niveau visé" for="propose-level" :error="$errors->first('proposeLevel')">
                    <select id="propose-level" wire:model="proposeLevel" class="sl-input w-full">
                        <option value="">Niveau…</option>
                        @foreach ($studyLevels as $studyLevel)
                            <option value="{{ $studyLevel->value }}">{{ $studyLevel->label() }}</option>
                        @endforeach
                    </select>
                </x-ui.field>

                <x-ui.field label="Filière" for="propose-filiere" :error="$errors->first('proposeFiliereId')">
                    <select id="propose-filiere" wire:model="proposeFiliereId" class="sl-input w-full">
                        <option value="">Filière…</option>
                        @foreach ($filieres as $filiere)
                            <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
            </div>

            <x-ui.field label="Lien GitHub (optionnel)" for="propose-repo" :error="$errors->first('proposeRepoUrl')">
                <input
                    id="propose-repo"
                    type="url"
                    wire:model="proposeRepoUrl"
                    class="sl-input w-full"
                    placeholder="https://github.com/..."
                />
            </x-ui.field>
        </div>

        <div class="sl-lib-drawer__foot">
            <button type="button" wire:click="closePropose" class="sl-btn sl-btn--secondary">Annuler</button>
            <button type="submit" class="sl-btn sl-btn--primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="submitPropose">Publier l'idée</span>
                <span wire:loading wire:target="submitPropose">Publication…</span>
            </button>
        </div>
    </form>
</aside>

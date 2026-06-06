@if ($uploadOpen)
    <button type="button" class="sl-lib-scrim is-open" wire:click="closeUpload" aria-label="Fermer le panneau de dépôt"></button>
@endif

<aside
    @class(['sl-lib-drawer', 'is-open' => $uploadOpen])
    role="dialog"
    aria-modal="true"
    aria-label="Déposer un document"
>
    <div class="sl-lib-drawer__head">
        <div>
            <h2 class="text-h3 font-semibold">Déposer un document</h2>
            <p class="text-sm text-muted">Partagez une ressource avec votre promo HESTIM.</p>
        </div>
        <button type="button" class="sl-icon-btn" wire:click="closeUpload" aria-label="Fermer">
            <x-ui.icon name="x" class="h-5 w-5" />
        </button>
    </div>

    <form wire:submit="submitUpload" class="flex min-h-0 flex-1 flex-col">
        <div class="sl-lib-drawer__body">
            @if (! $uploadFile)
                <div
                    class="sl-uploader"
                    x-data
                    x-on:dragover.prevent
                    x-on:drop.prevent="$refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }))"
                >
                    <input
                        x-ref="fileInput"
                        type="file"
                        wire:model="uploadFile"
                        accept=".pdf,.doc,.docx,.ppt,.pptx"
                        class="sr-only"
                        id="upload-file-input"
                    />
                    <label for="upload-file-input" class="block cursor-pointer">
                        <div class="sl-uploader-ico">
                            <x-ui.icon name="upload" class="h-6 w-6" />
                        </div>
                        <div class="sl-uploader-title">Glissez-déposez votre fichier</div>
                        <div class="sl-uploader-sub">ou <span class="font-semibold text-primary">parcourez vos fichiers</span> · PDF, DOCX, PPTX · 20 Mo max</div>
                    </label>
                </div>
            @else
                <div class="sl-lib-file-pill">
                    <span class="sl-lib-file-pill__ico">{{ strtoupper($uploadFile->getClientOriginalExtension() ?: 'FILE') }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-semibold">{{ $uploadFile->getClientOriginalName() }}</div>
                        <div class="text-xs text-muted">Prêt à envoyer</div>
                    </div>
                    <button type="button" class="sl-icon-btn" wire:click="removeUploadFile" aria-label="Retirer le fichier">
                        <x-ui.icon name="x" class="h-3.5 w-3.5" />
                    </button>
                </div>
            @endif

            @error('uploadFile')
                <p class="mt-2 text-sm text-danger" role="alert">{{ $message }}</p>
            @enderror

            <x-ui.field label="Titre du document" id="upload-title" class="mt-5" :error="$errors->first('uploadTitle')">
                <input id="upload-title" wire:model="uploadTitle" type="text" class="sl-input w-full @error('uploadTitle') is-error @enderror" placeholder="ex. Fiche de révision — Bases de données" />
            </x-ui.field>

            <x-ui.field label="Module" id="upload-module" class="mt-5" :error="$errors->first('uploadModuleId')">
                <select id="upload-module" wire:model="uploadModuleId" class="sl-input w-full @error('uploadModuleId') is-error @enderror">
                    <option value="">Sélectionnez un module</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module->id }}">{{ $module->name }}</option>
                    @endforeach
                </select>
            </x-ui.field>

            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.field label="Type" id="upload-type" :error="$errors->first('uploadType')">
                    <select id="upload-type" wire:model="uploadType" class="sl-input w-full @error('uploadType') is-error @enderror">
                        <option value="">Type…</option>
                        @foreach (\App\Enums\DocumentType::cases() as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </x-ui.field>

                <x-ui.field label="Année concernée" id="upload-year" :error="$errors->first('uploadYear')">
                    <select id="upload-year" wire:model="uploadYear" class="sl-input w-full">
                        <option value="">Année…</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year['value'] }}">{{ $year['label'] }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
            </div>

            <label class="mt-6 flex cursor-pointer gap-3 rounded-md border border-border bg-surface p-4">
                <input type="checkbox" wire:model="rightsAcknowledged" class="sr-only" />
                <span @class(['sl-lib-fbox', 'is-checked' => $rightsAcknowledged])>
                    <x-ui.icon name="check" class="h-3 w-3 text-white" />
                </span>
                <span class="text-sm text-ink-soft">
                    <strong class="font-semibold text-ink">J'atteste disposer des droits de partage</strong>
                    de ce document et accepte qu'il soit accessible aux membres vérifiés de la communauté HESTIM.
                </span>
            </label>
            @error('rightsAcknowledged')
                <p class="mt-2 text-sm text-danger" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="sl-lib-drawer__foot">
            <x-ui.button variant="secondary" type="button" wire:click="closeUpload">Annuler</x-ui.button>
            <x-ui.button variant="primary" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="submitUpload">Publier le document</span>
                <span wire:loading wire:target="submitUpload">Publication…</span>
            </x-ui.button>
        </div>
    </form>
</aside>

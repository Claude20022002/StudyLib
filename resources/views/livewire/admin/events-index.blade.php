<div>
    @if (session('success'))
        <x-ui.page-alert>{{ session('success') }}</x-ui.page-alert>
    @endif

    <div class="sl-adm-page-head">
        <div>
            <h2 class="text-h2 font-bold tracking-tight">Événements</h2>
            <p class="text-sm text-muted">Créez, modifiez ou supprimez les événements du campus.</p>
        </div>
        <button type="button" wire:click="openCreateForm" class="sl-btn sl-btn--primary">
            <x-ui.icon name="plus" class="h-4 w-4" />
            Nouvel événement
        </button>
    </div>

    <div class="sl-adm-kpi-grid">
        <div class="sl-adm-kpi">
            <div class="sl-adm-kpi__top">
                <div class="sl-adm-kpi__ico sl-adm-kpi__ico--flaticon">
                    <x-ui.flaticon name="calendar" class="sl-flaticon--kpi" />
                </div>
            </div>
            <div class="sl-adm-kpi__val">{{ $stats['upcoming'] }}</div>
            <p class="sl-adm-kpi__label">Événements à venir</p>
        </div>
        <div class="sl-adm-kpi">
            <div class="sl-adm-kpi__top">
                <div class="sl-adm-kpi__ico sl-adm-kpi__ico--flaticon">
                    <x-ui.flaticon name="calendar" class="sl-flaticon--kpi" />
                </div>
            </div>
            <div class="sl-adm-kpi__val">{{ $stats['this_month'] }}</div>
            <p class="sl-adm-kpi__label">Ce mois-ci</p>
        </div>
        <div class="sl-adm-kpi">
            <div class="sl-adm-kpi__top">
                <div class="sl-adm-kpi__ico sl-adm-kpi__ico--flaticon">
                    <x-ui.flaticon name="library" class="sl-flaticon--kpi" />
                </div>
            </div>
            <div class="sl-adm-kpi__val">{{ $stats['total'] }}</div>
            <p class="sl-adm-kpi__label">Total en base</p>
        </div>
    </div>

    <div class="sl-adm-toolbar">
        <div class="flex-1"></div>
        <div class="sl-adm-search">
            <x-ui.icon name="search" class="sl-adm-search__icon" />
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Filtrer par titre, lieu…"
                class="sl-adm-search__input"
                aria-label="Rechercher un événement"
            />
        </div>
    </div>

    <div class="sl-table-wrap">
        <div class="overflow-x-auto">
            <table class="sl-table">
                <thead>
                    <tr>
                        <th>Événement</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Lieu</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($eventRows as $event)
                        @php
                            $typeKey = $eventService->typeKey($event);
                        @endphp
                        <tr wire:key="event-{{ $event->id }}">
                            <td>
                                <div class="font-semibold text-ink">{{ $event->title }}</div>
                                @if ($event->description)
                                    <div class="mt-0.5 max-w-md truncate text-xs text-muted">{{ $event->description }}</div>
                                @endif
                            </td>
                            <td>
                                <span @class(['sl-ev-badge', 'sl-ev-type--'.$typeKey])>{{ $eventService->typeLabel($event) }}</span>
                            </td>
                            <td>
                                <div class="text-sm font-medium">{{ $event->starts_at->locale('fr')->translatedFormat('d M Y') }}</div>
                                <div class="text-xs text-muted">{{ $eventService->formatTime($event) }}</div>
                            </td>
                            <td class="text-sm text-muted">{{ $event->location ?? '·' }}</td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <button type="button" wire:click="openEditForm('{{ $event->id }}')" class="sl-btn sl-btn--secondary sl-btn--sm">
                                        Modifier
                                    </button>
                                    <button type="button" wire:click="openDeleteModal('{{ $event->id }}')" class="sl-btn sl-btn--ghost sl-btn--sm text-danger">
                                        <x-ui.icon name="x" class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="!py-10 text-center text-muted">
                                Aucun événement ne correspond à vos critères.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($eventRows->hasPages())
            <div class="border-t border-border px-4 py-3">
                {{ $eventRows->links('components.ui.pagination') }}
            </div>
        @else
            <div class="border-t border-border px-4 py-3 text-sm text-muted">
                {{ $eventRows->total() }} événement{{ $eventRows->total() > 1 ? 's' : '' }}
            </div>
        @endif
    </div>

    {{-- Form modal --}}
    @if ($formOpen)
        <button type="button" class="sl-adm-scrim" wire:click="closeForm" aria-label="Fermer"></button>
    @endif
    <div @class(['sl-adm-modal-wrap', 'is-open' => $formOpen]) role="dialog" aria-modal="true" aria-label="{{ $editingEventId ? 'Modifier l\'événement' : 'Nouvel événement' }}">
        <div class="sl-adm-modal sl-adm-modal--wide">
            <div class="sl-adm-modal__head">
                <div class="sl-adm-modal__icon sl-adm-kpi__ico--flaticon">
                    <x-ui.flaticon name="calendar" class="sl-flaticon--kpi" />
                </div>
                <div>
                    <h3 class="text-h3 font-semibold">{{ $editingEventId ? 'Modifier l\'événement' : 'Nouvel événement' }}</h3>
                    <p class="mt-1.5 text-sm text-ink-soft">Renseignez les informations de l'événement.</p>
                </div>
            </div>
            <div class="sl-adm-modal__body">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sl-lib-fgroup sm:col-span-2">
                        <label class="sl-lib-flabel" for="ev-title">Titre</label>
                        <input id="ev-title" type="text" wire:model="formTitle" class="sl-lib-fselect" maxlength="200" />
                        @error('formTitle') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="sl-lib-fgroup sm:col-span-2">
                        <label class="sl-lib-flabel" for="ev-desc">Description</label>
                        <textarea id="ev-desc" wire:model="formDescription" class="sl-adm-modal__textarea" rows="4"></textarea>
                        @error('formDescription') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="sl-lib-fgroup">
                        <label class="sl-lib-flabel" for="ev-start">Début</label>
                        <input id="ev-start" type="datetime-local" wire:model="formStartsAt" class="sl-lib-fselect" />
                        @error('formStartsAt') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="sl-lib-fgroup">
                        <label class="sl-lib-flabel" for="ev-end">Fin (optionnel)</label>
                        <input id="ev-end" type="datetime-local" wire:model="formEndsAt" class="sl-lib-fselect" />
                        @error('formEndsAt') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="sl-lib-fgroup sm:col-span-2">
                        <label class="sl-lib-flabel" for="ev-place">Lieu</label>
                        <input id="ev-place" type="text" wire:model="formLocation" class="sl-lib-fselect" maxlength="200" />
                        @error('formLocation') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="sl-lib-fgroup sm:col-span-2">
                        <label class="sl-lib-flabel" for="ev-image">Affiche (optionnel)</label>
                        <input id="ev-image" type="file" wire:model="formImage" accept="image/*" class="block w-full text-sm text-muted file:mr-3 file:rounded-md file:border-0 file:bg-primary-soft file:px-3 file:py-2 file:text-sm file:font-semibold file:text-primary" />
                        @error('formImage') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="formImage" class="mt-1 text-xs text-muted">Téléversement…</div>
                    </div>
                </div>
            </div>
            <div class="sl-adm-modal__foot">
                <button type="button" class="sl-btn sl-btn--secondary" wire:click="closeForm">Annuler</button>
                <button type="button" class="sl-btn sl-btn--primary" wire:click="save" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">Enregistrer</span>
                    <span wire:loading wire:target="save">Enregistrement…</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Delete modal --}}
    @if ($deleteModalOpen)
        <button type="button" class="sl-adm-scrim" wire:click="closeDeleteModal" aria-label="Fermer"></button>
    @endif
    <div @class(['sl-adm-modal-wrap', 'is-open' => $deleteModalOpen]) role="dialog" aria-modal="true" aria-label="Supprimer l'événement">
        <div class="sl-adm-modal">
            <div class="sl-adm-modal__head">
                <div class="sl-adm-modal__icon">
                    <x-ui.icon name="alert" class="h-[22px] w-[22px]" />
                </div>
                <div>
                    <h3 class="text-h3 font-semibold">Supprimer l'événement</h3>
                    <p class="mt-1.5 text-sm text-ink-soft">Cette action est irréversible.</p>
                </div>
            </div>
            @if ($deleteEvent)
                <div class="sl-adm-modal__body">
                    <div class="sl-adm-confirm-name">
                        <x-ui.icon name="calendar" class="h-4 w-4 text-muted" />
                        {{ $deleteEvent->title }}
                    </div>
                </div>
            @endif
            <div class="sl-adm-modal__foot">
                <button type="button" class="sl-btn sl-btn--secondary" wire:click="closeDeleteModal">Annuler</button>
                <button type="button" class="sl-btn sl-btn--danger" wire:click="confirmDelete" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="confirmDelete">Supprimer</span>
                    <span wire:loading wire:target="confirmDelete">Suppression…</span>
                </button>
            </div>
        </div>
    </div>
</div>

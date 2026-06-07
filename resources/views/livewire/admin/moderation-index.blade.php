<div>
    <div class="sl-adm-page-head">
        <div>
            <h2 class="text-h2 font-bold tracking-tight">Modération</h2>
            <p class="text-sm text-muted">Validez, refusez ou consultez les ressources déposées.</p>
        </div>
    </div>

    {{-- KPI --}}
    <div class="sl-adm-kpi-grid">
        <div @class(['sl-adm-kpi', 'sl-adm-kpi--alert' => $counts['pending'] > 0])>
            <div class="sl-adm-kpi__top">
                <div class="sl-adm-kpi__ico sl-adm-kpi__ico--warning">
                    <x-ui.icon name="upload" class="h-5 w-5" />
                </div>
                <x-ui.badge variant="warning">À traiter</x-ui.badge>
            </div>
            <div class="sl-adm-kpi__val">{{ $counts['pending'] }}</div>
            <p class="sl-adm-kpi__label">Uploads en attente</p>
            @if ($counts['pending'] > 0)
                <button type="button" class="sl-adm-kpi__link" wire:click="setStatusFilter('pending')">
                    Modérer
                    <x-ui.icon name="arrow-right" class="h-3 w-3" />
                </button>
            @endif
        </div>

        <div class="sl-adm-kpi">
            <div class="sl-adm-kpi__top">
                <div class="sl-adm-kpi__ico sl-adm-kpi__ico--success">
                    <x-ui.icon name="check" class="h-5 w-5" />
                </div>
                <x-ui.badge variant="neutral">Total</x-ui.badge>
            </div>
            <div class="sl-adm-kpi__val">{{ $counts['approved'] }}</div>
            <p class="sl-adm-kpi__label">Documents validés</p>
        </div>

        <div class="sl-adm-kpi">
            <div class="sl-adm-kpi__top">
                <div class="sl-adm-kpi__ico sl-adm-kpi__ico--danger">
                    <x-ui.icon name="x" class="h-5 w-5" />
                </div>
            </div>
            <div class="sl-adm-kpi__val">{{ $counts['rejected'] }}</div>
            <p class="sl-adm-kpi__label">Documents refusés</p>
        </div>

        <div class="sl-adm-kpi">
            <div class="sl-adm-kpi__top">
                <div class="sl-adm-kpi__ico sl-adm-kpi__ico--primary">
                    <x-ui.icon name="library" class="h-5 w-5" />
                </div>
            </div>
            <div class="sl-adm-kpi__val">{{ $counts['all'] }}</div>
            <p class="sl-adm-kpi__label">Documents au total</p>
        </div>
    </div>

    @php
        $quickPending = $statusFilter === 'pending' ? collect($documents->items())->take(3) : collect();
    @endphp

    @if ($counts['pending'] > 0 && $statusFilter === 'pending' && $quickPending->isNotEmpty() && $documents->currentPage() === 1 && $search === '')
        <div class="sl-adm-card mb-5">
            <div class="sl-adm-card__head">
                <h3 class="text-h4 font-semibold">Uploads en attente de validation</h3>
            </div>
            @foreach ($quickPending as $document)
                <x-admin.moderation-queue-row :document="$document" wire:key="queue-{{ $document->id }}" />
            @endforeach
        </div>
    @endif

    {{-- Toolbar --}}
    <div class="sl-adm-toolbar">
        <div class="sl-adm-tabs" role="tablist" aria-label="Filtrer par statut">
            @foreach ([
                'all' => 'Tous',
                'pending' => 'En attente',
                'approved' => 'Validés',
                'rejected' => 'Refusés',
            ] as $value => $label)
                <button
                    type="button"
                    role="tab"
                    wire:click="setStatusFilter('{{ $value }}')"
                    @class(['sl-adm-tab', 'is-active' => $statusFilter === $value])
                    aria-selected="{{ $statusFilter === $value ? 'true' : 'false' }}"
                >
                    {{ $label }}
                    <span class="sl-adm-tab__count">{{ $counts[$value] ?? 0 }}</span>
                </button>
            @endforeach
        </div>
        <div class="flex-1"></div>
        <div class="sl-adm-search">
            <x-ui.icon name="search" class="sl-adm-search__icon" />
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Filtrer par titre, auteur…"
                class="sl-adm-search__input"
                aria-label="Rechercher dans les documents"
            />
        </div>
    </div>

    {{-- Table --}}
    <div class="sl-table-wrap">
        <div class="overflow-x-auto">
            <table class="sl-table">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Module</th>
                        <th>Auteur</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                        <x-admin.moderation-table-row :document="$document" wire:key="doc-{{ $document->id }}" />
                    @empty
                        <tr>
                            <td colspan="7" class="!py-10 text-center text-muted">
                                Aucun document ne correspond à vos critères.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($documents->hasPages())
            <div class="border-t border-border px-4 py-3">
                {{ $documents->links('components.ui.pagination') }}
            </div>
        @else
            <div class="border-t border-border px-4 py-3 text-sm text-muted">
                {{ $documents->total() }} document{{ $documents->total() > 1 ? 's' : '' }}
            </div>
        @endif
    </div>

    {{-- Reject modal --}}
    @if ($rejectModalOpen)
        <button type="button" class="sl-adm-scrim" wire:click="closeRejectModal" aria-label="Fermer"></button>
    @endif
    <div @class(['sl-adm-modal-wrap', 'is-open' => $rejectModalOpen]) role="dialog" aria-modal="true" aria-label="Refuser le document">
        <div class="sl-adm-modal">
            <div class="sl-adm-modal__head">
                <div class="sl-adm-modal__icon">
                    <x-ui.icon name="x" class="h-[22px] w-[22px]" />
                </div>
                <div>
                    <h3 class="text-h3 font-semibold">Refuser le document</h3>
                    <p class="mt-1.5 text-sm text-ink-soft">L'auteur sera notifié. Vous pouvez indiquer une raison optionnelle.</p>
                </div>
            </div>
            @if ($rejectDocument)
                <div class="sl-adm-modal__body">
                    <div class="sl-adm-confirm-name">
                        <x-ui.icon name="file" class="h-4 w-4 text-muted" />
                        {{ $rejectDocument->title }}
                    </div>
                    <label class="mt-4 block text-sm font-semibold text-ink-soft" for="reject-reason">Motif (optionnel)</label>
                    <textarea
                        id="reject-reason"
                        wire:model="rejectReason"
                        class="sl-adm-modal__textarea"
                        rows="3"
                        maxlength="500"
                        placeholder="Ex. hors-sujet, qualité insuffisante, droits non respectés…"
                    ></textarea>
                </div>
            @endif
            <div class="sl-adm-modal__foot">
                <button type="button" class="sl-btn sl-btn--secondary" wire:click="closeRejectModal">Annuler</button>
                <button type="button" class="sl-btn sl-btn--danger" wire:click="submitReject" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submitReject">Refuser le document</span>
                    <span wire:loading wire:target="submitReject">Refus…</span>
                </button>
            </div>
        </div>
    </div>
</div>

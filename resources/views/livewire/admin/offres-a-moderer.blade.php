<div>
    @if ($offres->isNotEmpty())
        {{-- Offres de stage partagées par des étudiants : on vérifie le lien avant de publier --}}
        <div class="sl-table-wrap mb-6">
            <div class="px-4 pt-4">
                <h3 class="text-base font-semibold">Offres de stage à vérifier ({{ $offres->count() }})</h3>
                <p class="text-sm text-muted">Ouvrez le lien pour vérifier que l’offre existe et correspond, puis publiez-la ou refusez-la.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="sl-table">
                    <thead>
                        <tr>
                            <th>Offre</th>
                            <th>Lien</th>
                            <th>Partagée par</th>
                            <th>Date limite</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($offres as $offre)
                            <tr wire:key="offre-{{ $offre->id }}">
                                <td>
                                    <div class="font-medium">{{ $offre->title }}</div>
                                    <div class="text-sm text-muted">{{ $offre->company_name }}{{ $offre->city ? ' · '.$offre->city : '' }}</div>
                                </td>
                                <td>
                                    <a href="{{ $offre->apply_url }}" target="_blank" rel="noopener noreferrer nofollow" class="underline">{{ parse_url($offre->apply_url, PHP_URL_HOST) }}</a>
                                </td>
                                <td>{{ $offre->author?->name ?? '—' }}</td>
                                <td>{{ $offre->deadline?->format('d/m/Y') ?? '—' }}</td>
                                <td class="text-right">
                                    <div class="inline-flex gap-2">
                                        <button type="button" class="sl-btn sl-btn--success sl-btn--xs" wire:click="approuver('{{ $offre->id }}')" wire:loading.attr="disabled">
                                            <x-ui.icon name="check" />
                                            Publier
                                        </button>
                                        <button type="button" class="sl-btn sl-btn--secondary sl-btn--xs" wire:click="refuser('{{ $offre->id }}')" wire:loading.attr="disabled">
                                            Refuser
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

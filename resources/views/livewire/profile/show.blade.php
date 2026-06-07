<div>
    @if ($user)
        @php
            $initials = collect(preg_split('/\s+/', trim($user->name)) ?: [])
                ->take(2)
                ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
                ->implode('');
            $avgRating = $stats['avg_document_rating'] ?? 0;
            $trustPct = min(100, (int) round(($avgRating / 5) * 100));
            $trustLabel = match (true) {
                $avgRating >= 4.5 => 'Contributeur de confiance',
                $avgRating >= 3.5 => 'Contributeur actif',
                $avgRating > 0 => 'Contributeur en progression',
                default => 'Nouveau contributeur',
            };
        @endphp

        <header class="sl-prof-head">
            <div class="sl-prof-head__cover" aria-hidden="true"></div>
            <div class="sl-prof-head__body">
                <x-ui.avatar :initials="$initials !== '' ? $initials : '?'" class="sl-prof-head__avatar !h-[104px] !w-[104px] !text-[38px]" />
                <div class="min-w-0 flex-1 pt-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-h1 leading-tight font-bold tracking-tight">{{ $user->name }}</h2>
                        @if ($user->email_verified_at)
                            <span class="sl-prof-verified">
                                <x-ui.icon name="check" class="h-3.5 w-3.5" />
                                Email HESTIM vérifié
                            </span>
                        @endif
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-muted">
                        @if ($user->filiere)
                            <span class="inline-flex items-center gap-1.5">
                                <x-ui.icon name="layers" class="h-3.5 w-3.5" />
                                {{ $user->filiere->name }}
                            </span>
                        @endif
                        @if ($user->year_level)
                            <span class="inline-flex items-center gap-1.5">
                                <x-ui.icon name="user" class="h-3.5 w-3.5" />
                                L{{ $user->year_level }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5">
                            <x-ui.icon name="mail" class="h-3.5 w-3.5" />
                            {{ $user->email }}
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <x-ui.icon name="calendar" class="h-3.5 w-3.5" />
                            Membre depuis {{ $user->created_at?->locale('fr')->isoFormat('MMM YYYY') }}
                        </span>
                    </div>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" class="sl-btn sl-btn--secondary" wire:click="setTab('params')">
                        <x-ui.icon name="user" />
                        Modifier
                    </button>
                    <x-ui.button variant="primary" href="{{ route('documents.index', ['upload' => 1]) }}" wire:navigate>
                        <x-ui.icon name="plus" />
                        Déposer
                    </x-ui.button>
                </div>
            </div>
        </header>

        <div class="sl-prof-overview">
            <div class="sl-prof-stats">
                <div class="sl-prof-stat">
                    <div class="sl-prof-stat__ico sl-prof-stat__ico--primary">
                        <x-ui.icon name="file" class="h-[22px] w-[22px]" />
                    </div>
                    <div>
                        <div class="sl-prof-stat__val">{{ $stats['documents_count'] ?? 0 }}</div>
                        <p class="sl-prof-stat__label">Documents déposés</p>
                    </div>
                </div>
                <div class="sl-prof-stat">
                    <div class="sl-prof-stat__ico sl-prof-stat__ico--success">
                        <x-ui.icon name="download" class="h-[22px] w-[22px]" />
                    </div>
                    <div>
                        <div class="sl-prof-stat__val">{{ number_format($stats['downloads_received'] ?? 0, 0, ',', ' ') }}</div>
                        <p class="sl-prof-stat__label">Téléchargements reçus</p>
                    </div>
                </div>
                <div class="sl-prof-stat">
                    <div class="sl-prof-stat__ico sl-prof-stat__ico--warning">
                        <x-ui.icon name="briefcase" class="h-[22px] w-[22px]" />
                    </div>
                    <div>
                        <div class="sl-prof-stat__val">{{ $stats['internship_reviews_count'] ?? 0 }}</div>
                        <p class="sl-prof-stat__label">Avis de stage publiés</p>
                    </div>
                </div>
                <div class="sl-prof-stat">
                    <div class="sl-prof-stat__ico sl-prof-stat__ico--purple">
                        <x-ui.icon name="layers" class="h-[22px] w-[22px]" />
                    </div>
                    <div>
                        <div class="sl-prof-stat__val">{{ $stats['project_ideas_count'] ?? 0 }}</div>
                        <p class="sl-prof-stat__label">Projets partagés</p>
                    </div>
                </div>
            </div>

            <aside class="sl-prof-trust">
                <h3 class="flex items-center gap-2 text-h4 font-semibold">
                    <x-ui.icon name="shield-check" class="h-[18px] w-[18px] text-primary" />
                    Indice de confiance
                </h3>
                <div class="mt-4 flex items-center gap-4">
                    <div class="sl-prof-trust__ring" style="--pct: {{ $trustPct }};">
                        <div class="sl-prof-trust__ring-inner">
                            <span class="text-h3 leading-none font-bold">{{ number_format($avgRating, 1, ',', ' ') }}</span>
                            <span class="text-[10px] font-semibold text-muted">/ 5</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-h4 font-bold text-success-ink">{{ $trustLabel }}</div>
                        <p class="mt-0.5 text-sm text-muted">
                            @if (($stats['ratings_received'] ?? 0) > 0)
                                Basé sur {{ $stats['ratings_received'] }} notes reçues sur vos dépôts.
                            @else
                                Déposez des ressources pour recevoir des notes de la promo.
                            @endif
                        </p>
                    </div>
                </div>
            </aside>
        </div>

        <div class="sl-prof-split">
            <section>
                <div class="sl-prof-tabs" role="tablist" aria-label="Sections du profil">
                    @foreach ([
                        'docs' => ['label' => 'Mes documents', 'icon' => 'file', 'count' => $stats['documents_count'] ?? 0],
                        'stages' => ['label' => 'Mes stages', 'icon' => 'briefcase', 'count' => $stats['internship_reviews_count'] ?? 0],
                        'projets' => ['label' => 'Mes projets', 'icon' => 'layers', 'count' => $stats['project_ideas_count'] ?? 0],
                        'favoris' => ['label' => 'Favoris', 'icon' => 'bookmark', 'count' => 0],
                        'params' => ['label' => 'Paramètres', 'icon' => 'user', 'count' => null],
                    ] as $key => $meta)
                        <button
                            type="button"
                            role="tab"
                            wire:click="setTab('{{ $key }}')"
                            @class(['sl-prof-tab', 'is-active' => $tab === $key])
                            aria-selected="{{ $tab === $key ? 'true' : 'false' }}"
                        >
                            <x-ui.icon :name="$meta['icon']" class="h-4 w-4" />
                            {{ $meta['label'] }}
                            @if ($meta['count'] !== null)
                                <span class="sl-prof-tab__count">{{ $meta['count'] }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>

                @if ($tab === 'docs')
                    <div class="sl-prof-item-list">
                        @forelse ($documents as $document)
                            <x-profile.document-item :document="$document" wire:key="prof-doc-{{ $document->id }}" />
                        @empty
                            <x-ui.empty-state flaticon="library" title="Aucun document" description="Utilisez le bouton « Déposer » en haut de votre profil pour partager votre première ressource." />
                        @endforelse
                    </div>
                @elseif ($tab === 'stages')
                    <div class="sl-prof-item-list">
                        @forelse ($internshipReviews as $review)
                            <x-profile.internship-item :review="$review" wire:key="prof-stage-{{ $review->id }}" />
                        @empty
                            <x-ui.empty-state flaticon="briefcase" title="Aucun avis de stage" description="Partagez votre retour d'expérience pour aider la promo.">
                                <x-ui.button variant="primary" href="{{ route('internship-reviews.index') }}" wire:navigate>
                                    Voir les stages
                                </x-ui.button>
                            </x-ui.empty-state>
                        @endforelse
                    </div>
                @elseif ($tab === 'projets')
                    <div class="sl-prof-item-list">
                        @forelse ($projectIdeas as $idea)
                            <x-profile.project-item :idea="$idea" wire:key="prof-proj-{{ $idea->id }}" />
                        @empty
                            <x-ui.empty-state flaticon="idea" title="Aucun projet" description="Partagez une idée de projet CV avec la communauté.">
                                <x-ui.button variant="primary" href="{{ route('project-ideas.index') }}" wire:navigate>
                                    Explorer les projets
                                </x-ui.button>
                            </x-ui.empty-state>
                        @endforelse
                    </div>
                @elseif ($tab === 'favoris')
                    <x-ui.empty-state flaticon="empty" title="Aucun favori" description="Les favoris seront disponibles prochainement. Enregistrez vos meilleures ressources pour les retrouver rapidement.">
                    </x-ui.empty-state>
                @else
                    <form wire:submit="saveProfile" class="sl-prof-settings">
                        <div class="sl-prof-set-group">
                            <div class="sl-prof-set-group__head">
                                <h3 class="text-h4 font-semibold">Informations personnelles</h3>
                                <p class="text-xs text-muted">Mettez à jour votre nom et votre filière.</p>
                            </div>
                            <div class="sl-field px-5 py-4">
                                <label for="prof-name">Nom complet</label>
                                <input id="prof-name" type="text" wire:model="name" class="sl-input w-full" />
                                @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>
                            <div class="sl-field border-t border-border px-5 py-4">
                                <label for="prof-filiere">Filière</label>
                                <select id="prof-filiere" wire:model="filiereId" class="sl-input w-full">
                                    <option value="">Sélectionnez une filière</option>
                                    @foreach ($filieres as $filiere)
                                        <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                                    @endforeach
                                </select>
                                @error('filiereId') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>
                            <div class="sl-field border-t border-border px-5 py-4">
                                <label for="prof-level">Niveau</label>
                                <select id="prof-level" wire:model="yearLevel" class="sl-input w-full">
                                    <option value="">Niveau…</option>
                                    @for ($level = 1; $level <= 5; $level++)
                                        <option value="{{ $level }}">L{{ $level }}</option>
                                    @endfor
                                </select>
                                @error('yearLevel') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>
                            <div class="border-t border-border px-5 py-4">
                                <button type="submit" class="sl-btn sl-btn--primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="saveProfile">Enregistrer</span>
                                    <span wire:loading wire:target="saveProfile">Enregistrement…</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="sl-prof-set-group sl-prof-set-group--danger">
                        <div class="sl-prof-set-group__head">
                            <h3 class="text-h4 font-semibold text-danger-ink">Compte</h3>
                            <p class="text-xs text-muted">Actions liées à votre session.</p>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-5 py-4">
                            <div>
                                <div class="text-sm font-semibold">Se déconnecter</div>
                                <div class="text-xs text-muted">Fermer votre session sur cet appareil</div>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="sl-btn sl-btn--danger">
                                    Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </section>

            <aside class="sl-prof-rail" aria-label="Activité récente">
                <div class="sl-prof-rail-panel">
                    <h3 class="text-h4 font-semibold">Contributions récentes</h3>
                    @if ($recentActivity->isEmpty())
                        <p class="mt-4 text-sm text-muted">Vos dernières actions apparaîtront ici.</p>
                    @else
                        <div class="sl-prof-timeline">
                            @foreach ($recentActivity as $item)
                                <div class="sl-prof-timeline__item" wire:key="activity-{{ $loop->index }}">
                                    <span @class(['sl-prof-timeline__dot', 'sl-prof-timeline__dot--'.$item['tone']])></span>
                                    <div>
                                        <div class="text-xs text-muted">{{ $item['time'] }}</div>
                                        <p class="mt-0.5 text-sm text-ink">{{ $item['text'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </aside>
        </div>
    @endif
</div>

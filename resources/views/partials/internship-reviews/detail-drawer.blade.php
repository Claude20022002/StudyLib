@if ($detailOpen && $detail)
    <button type="button" class="sl-lib-scrim is-open" wire:click="closeDetail" aria-label="Fermer la fiche entreprise"></button>
@endif

<aside
    @class(['sl-lib-drawer', 'is-open' => $detailOpen && $detail])
    role="dialog"
    aria-modal="true"
    aria-label="Fiche entreprise"
>
    @if ($detail)
        @php
            $company = $detail['company'];
            $accent = $reviewService->companyAccentColor($company->name);
            $initials = $reviewService->companyInitials($company->name);
            $totalReviews = max(1, (int) $detail['reviews_count']);
        @endphp

        <div class="sl-lib-drawer__head">
            <span class="text-sm font-semibold text-muted">Fiche entreprise</span>
            <button type="button" class="sl-icon-btn" wire:click="closeDetail" aria-label="Fermer">
                <x-ui.icon name="x" class="h-5 w-5" />
            </button>
        </div>

        <div class="sl-lib-drawer__body">
            <div class="sl-stg-det-head">
                <div class="sl-stg-det-logo" style="background: {{ $accent }}">{{ $initials }}</div>
                <div>
                    <h2 class="sl-stg-det-name">{{ $company->name }}</h2>
                    <p class="sl-stg-det-sub">
                        <x-ui.icon name="map-pin" class="h-3.5 w-3.5" />
                        {{ collect([$company->city, $company->sector])->filter()->implode(' · ') ?: 'Informations limitées' }}
                    </p>
                </div>
            </div>

            <div class="sl-stg-score">
                <div class="sl-stg-score__big">
                    <div class="sl-stg-score__num">{{ number_format((float) $detail['avg_rating'], 1, ',', '') }}</div>
                    <div class="sl-stg-score__of">sur 5</div>
                </div>
                <div class="sl-stg-score__bars">
                    @foreach ([5, 4, 3, 2, 1] as $stars)
                        @php
                            $count = $detail['distribution'][$stars] ?? 0;
                            $percent = (int) round(($count / $totalReviews) * 100);
                        @endphp
                        <div class="sl-stg-score__line">
                            <span class="sl-stg-score__label">{{ $stars }}★</span>
                            <div class="sl-stg-score__bar"><span style="width: {{ $percent }}%"></span></div>
                            <span class="sl-stg-score__val">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <section class="sl-stg-det-section">
                <h3 class="sl-stg-det-section__title">
                    <x-ui.icon name="message" class="h-4 w-4 text-primary" />
                    Retours de stagiaires ({{ $detail['reviews_count'] }})
                </h3>

                @forelse ($company->internshipReviews as $review)
                    <x-internships.review-card :review="$review" :review-service="$reviewService" wire:key="review-{{ $review->id }}" />
                @empty
                    <p class="text-sm text-muted">Aucun avis pour le moment.</p>
                @endforelse
            </section>
        </div>

        <div class="sl-lib-drawer__foot">
            <button type="button" wire:click="closeDetail" class="sl-btn sl-btn--secondary">Fermer</button>
            <button type="button" wire:click="openShare('{{ $company->name }}')" class="sl-btn sl-btn--primary">
                <x-ui.icon name="plus" class="h-4 w-4" />
                Ajouter mon retour
            </button>
        </div>
    @endif
</aside>

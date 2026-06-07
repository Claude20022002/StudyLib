@props([
    'company',
    'reviewService',
])

@php
    $avgRating = (float) ($company->avg_rating ?? 0);
    $reviewsCount = (int) ($company->reviews_count ?? 0);
    $accent = $reviewService->companyAccentColor($company->name);
    $initials = $reviewService->companyInitials($company->name);
    $recommended = $avgRating >= 4.5 && $reviewsCount >= 2;
@endphp

<article
    {{ $attributes->merge(['class' => 'sl-stg-company']) }}
    wire:click="openDetail('{{ $company->id }}')"
    role="button"
    tabindex="0"
    wire:key="company-{{ $company->id }}"
>
    <div class="sl-stg-company__top">
        <div class="sl-stg-company__logo" style="background: {{ $accent }}">{{ $initials }}</div>
        <div class="min-w-0">
            <h3 class="sl-stg-company__name">{{ $company->name }}</h3>
            <p class="sl-stg-company__loc">
                <x-ui.icon name="map-pin" class="h-3.5 w-3.5 shrink-0" />
                {{ collect([$company->city, $company->sector])->filter()->implode(' · ') ?: 'Localisation non renseignée' }}
            </p>
        </div>
    </div>

    <div class="sl-stg-company__rating">
        <span class="sl-stg-company__rate-num">{{ number_format($avgRating, 1, ',', '') }}</span>
        <x-ui.star-rating :value="$avgRating" :max="5" />
        <span class="sl-stg-company__reviews">{{ $reviewsCount }} avis</span>
    </div>

    <div class="sl-stg-company__foot">
        @if ($recommended)
            <span class="sl-stg-reco">
                <x-ui.icon name="check" class="h-3 w-3" />
                Recommandé
            </span>
        @else
            <span class="text-xs text-muted">{{ $company->sector ?: 'Stage' }}</span>
        @endif
        <span class="text-sm font-semibold text-primary">Voir la fiche →</span>
    </div>
</article>

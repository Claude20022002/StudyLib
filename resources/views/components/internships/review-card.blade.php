@props([
    'review',
    'reviewService',
])

@php
    $author = $reviewService->maskedAuthorName($review);
    $initials = $reviewService->companyInitials($author);
    $meta = collect([
        $review->filiere?->name,
        $review->year_level ? 'L'.$review->year_level : null,
        $review->year_done,
    ])->filter()->implode(' · ');
@endphp

<article {{ $attributes->merge(['class' => 'sl-stg-review']) }}>
    <div class="sl-stg-review__head">
        <div class="sl-stg-review__av" aria-hidden="true">{{ $initials }}</div>
        <div>
            <div class="sl-stg-review__who">{{ $author }}</div>
            @if ($meta !== '')
                <div class="sl-stg-review__meta">{{ $meta }}</div>
            @endif
        </div>
        <div class="ml-auto flex items-center gap-2">
            <x-ui.star-rating :value="$review->rating" :max="5" />
            @if ($review->is_paid)
                <x-ui.badge variant="success">Rémunéré</x-ui.badge>
            @endif
        </div>
    </div>

    @if ($review->position)
        <div class="sl-stg-review__block">
            <div class="sl-stg-review__lbl">Poste</div>
            <p>{{ $review->position }}</p>
        </div>
    @endif

    <div class="sl-stg-review__block">
        <div class="sl-stg-review__lbl">Retour d'expérience</div>
        <p>{{ $review->description }}</p>
    </div>

    <p class="sl-stg-review__anon">
        <x-ui.icon name="shield" class="h-3.5 w-3.5" />
        Identité partiellement masquée pour protéger la vie privée.
    </p>
</article>

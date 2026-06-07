@props([
    'review',
])

<article {{ $attributes->merge(['class' => 'sl-prof-item']) }}>
    <div class="sl-prof-item__ico sl-prof-item__ico--stage" aria-hidden="true">
        <x-ui.icon name="briefcase" class="h-5 w-5" />
    </div>
    <div class="min-w-0 flex-1">
        <div class="mb-1 flex flex-wrap gap-1.5">
            @if ($review->company)
                <x-ui.badge variant="primary">{{ $review->company->name }}</x-ui.badge>
            @endif
            @if ($review->year_done)
                <x-ui.badge variant="neutral">{{ $review->year_done }}</x-ui.badge>
            @endif
        </div>
        <h3 class="text-h4 leading-snug font-semibold">{{ $review->position }}</h3>
        <div class="mt-1.5 flex flex-wrap items-center gap-4 text-sm text-muted">
            @if ($review->rating)
                <x-ui.star-rating :value="$review->rating" :max="5" />
                <span>Noté {{ $review->rating }}/5</span>
            @endif
            <span>{{ $review->created_at?->diffForHumans() }}</span>
        </div>
    </div>
    <div class="flex shrink-0 gap-2">
        <x-ui.button variant="ghost" size="sm" href="{{ route('internship-reviews.index') }}" wire:navigate>
            Voir
        </x-ui.button>
    </div>
</article>

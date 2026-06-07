@props([
    'document',
])

@php
    $colors = $document->thumbColors();
    $rating = number_format($document->avg_rating, 1, ',', ' ');
@endphp

<a href="{{ route('documents.show', $document) }}" wire:navigate {{ $attributes->merge(['class' => 'sl-doc-mini']) }}>
    <div
        class="sl-doc-mini__ico"
        style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }};"
        aria-hidden="true"
    >
        {{ $document->fileKindLabel() }}
    </div>
    <div class="sl-doc-mini__info">
        <h4>{{ $document->title }}</h4>
        <p>
            {{ $document->type->label() }}
            ·
            <span class="inline-flex items-center gap-0.5">
                <svg class="h-[11px] w-[11px] text-star" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 5.9 6.5.95-4.7 4.58 1.1 6.47L12 17.4l-5.8 3.05 1.1-6.47L2.6 9.35l6.5-.95z"/></svg>
                {{ $rating }}
            </span>
        </p>
    </div>
</a>

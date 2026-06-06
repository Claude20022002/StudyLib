@props([
    'document',
])

@php
    $colors = $document->thumbColors();
    $rating = (int) round($document->avg_rating);
@endphp

<article class="sl-doc">
    <div
        class="sl-doc-thumb"
        style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }};"
        aria-hidden="true"
    >
        {{ $document->fileKindLabel() }}
    </div>

    <div class="sl-doc-main min-w-0 flex-1">
        <div class="mb-1.5 flex flex-wrap gap-1.5">
            <x-ui.badge :variant="$document->type->badgeVariant()">{{ $document->type->label() }}</x-ui.badge>
            @if ($document->module)
                <x-ui.badge variant="neutral">{{ $document->module->name }}</x-ui.badge>
                <x-ui.badge variant="neutral">S{{ $document->module->semester }}</x-ui.badge>
            @endif
        </div>
        <h3 class="sl-doc-title">
            <a href="{{ route('documents.show', $document) }}" wire:navigate class="hover:text-primary">
                {{ $document->title }}
            </a>
        </h3>
        <div class="sl-doc-sub">
            @if ($document->author)
                <span>par {{ $document->author->name }}</span>
            @endif
            <span class="inline-flex items-center gap-1.5">
                <x-ui.star-rating :value="$rating" class="gap-0">
                    <strong class="text-ink-soft">{{ number_format($document->avg_rating, 1, ',', ' ') }}</strong>
                </x-ui.star-rating>
            </span>
            <span class="inline-flex items-center gap-1 text-sm text-muted">
                <x-ui.icon name="download" class="h-3.5 w-3.5" />
                {{ $document->downloads_count }}
            </span>
        </div>
    </div>

    <div class="sl-doc-actions">
        <button type="button" class="sl-icon-btn" aria-label="Favori" disabled>
            <x-ui.icon name="bookmark" class="h-[19px] w-[19px]" />
        </button>
        <x-ui.button variant="primary" size="sm" href="{{ route('documents.show', $document) }}">
            <x-ui.icon name="download" />
            Télécharger
        </x-ui.button>
    </div>
</article>

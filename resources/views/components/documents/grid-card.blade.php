@props([
    'document',
])

@php
    $colors = $document->thumbColors();
    $rating = (int) round($document->avg_rating);
@endphp

<article class="sl-lib-card">
    <div class="sl-lib-card__thumb">
        <span class="absolute top-2.5 left-2.5">
            <x-ui.badge :variant="$document->type->badgeVariant()">{{ $document->type->label() }}</x-ui.badge>
        </span>
        <div class="sl-lib-card__pdf" style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }};">
            {{ $document->fileKindLabel() }}
        </div>
    </div>
    <div class="flex flex-1 flex-col gap-3 p-4">
        <div class="flex flex-wrap gap-1.5">
            @if ($document->module)
                <x-ui.badge variant="neutral">{{ $document->module->name }}</x-ui.badge>
                <x-ui.badge variant="neutral">S{{ $document->module->semester }}</x-ui.badge>
            @endif
        </div>
        <h3 class="text-h4 leading-snug font-semibold text-pretty">
            <a href="{{ route('documents.show', $document) }}" wire:navigate class="hover:text-primary">
                {{ $document->title }}
            </a>
        </h3>
        <div class="mt-auto flex flex-wrap items-center gap-3 text-sm text-muted">
            @if ($document->author)
                <span>{{ $document->author->name }}</span>
            @endif
            <x-ui.star-rating :value="$rating" />
            <span>{{ $document->downloads_count }} téléch.</span>
        </div>
    </div>
    <div class="flex gap-2 px-4 pb-4">
        <x-ui.button variant="secondary" size="sm" class="flex-1" href="{{ route('documents.show', $document) }}" wire:navigate>
            Voir
        </x-ui.button>
        <form method="POST" action="{{ route('documents.download', $document) }}" class="flex-1">
            @csrf
            <x-ui.button variant="primary" size="sm" class="w-full" type="submit">
                <x-ui.icon name="download" />
            </x-ui.button>
        </form>
    </div>
</article>

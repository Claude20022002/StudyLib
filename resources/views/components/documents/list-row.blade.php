@props([
    'document',
])

@php
    $colors = $document->thumbColors();
    $rating = (int) round($document->avg_rating);
@endphp

<article class="sl-lib-row">
    <div
        class="sl-lib-row__ico"
        style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }};"
        aria-hidden="true"
    >
        {{ $document->fileKindLabel() }}
    </div>

    <div class="min-w-0 flex-1">
        <div class="mb-1.5 flex flex-wrap gap-1.5">
            <x-ui.badge :variant="$document->type->badgeVariant()">{{ $document->type->label() }}</x-ui.badge>
            @if ($document->module)
                <x-ui.badge variant="neutral">{{ $document->module->name }}</x-ui.badge>
                <x-ui.badge variant="neutral">S{{ $document->module->semester }}</x-ui.badge>
            @endif
            @if ($document->status !== \App\Enums\DocumentStatus::Approved)
                <x-ui.badge variant="warning">En modération</x-ui.badge>
            @endif
        </div>
        <h3 class="sl-lib-row__title">
            <a href="{{ route('documents.show', $document) }}" wire:navigate class="hover:text-primary">
                {{ $document->title }}
            </a>
        </h3>
        <div class="sl-lib-row__meta">
            @if ($document->author)
                <span class="inline-flex items-center gap-1">
                    <x-ui.icon name="user" class="h-3.5 w-3.5" />
                    {{ $document->author->name }}
                </span>
            @endif
            @if ($document->year_concern)
                <span class="inline-flex items-center gap-1">
                    <x-ui.icon name="calendar" class="h-3.5 w-3.5" />
                    {{ $document->year_concern }}-{{ $document->year_concern + 1 }}
                </span>
            @endif
            <span class="inline-flex items-center gap-1">
                <x-ui.icon name="download" class="h-3.5 w-3.5" />
                {{ $document->downloads_count }} téléch.
            </span>
            <span class="inline-flex items-center gap-1">
                <x-ui.star-rating :value="$rating" class="gap-0">
                    <strong class="text-ink-soft">{{ number_format($document->avg_rating, 1, ',', ' ') }}</strong>
                </x-ui.star-rating>
            </span>
            <span>{{ $document->created_at?->diffForHumans() }}</span>
        </div>
    </div>

    <div class="sl-lib-row__actions">
        <x-ui.button variant="secondary" size="sm" href="{{ route('documents.show', $document) }}" wire:navigate>
            Voir
        </x-ui.button>
        <form method="POST" action="{{ route('documents.download', $document) }}">
            @csrf
            <x-ui.button variant="primary" size="sm" type="submit">
                <x-ui.icon name="download" />
                Télécharger
            </x-ui.button>
        </form>
    </div>
</article>

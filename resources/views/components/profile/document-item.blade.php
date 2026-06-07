@props([
    'document',
])

@php
    $colors = $document->thumbColors();
    $statusBadge = match ($document->status) {
        \App\Enums\DocumentStatus::Approved => ['variant' => 'success', 'label' => 'Validé'],
        \App\Enums\DocumentStatus::Pending => ['variant' => 'warning', 'label' => 'En attente'],
        \App\Enums\DocumentStatus::Rejected => ['variant' => 'danger', 'label' => 'Refusé'],
    };
    $rating = (int) round($document->avg_rating);
@endphp

<article {{ $attributes->merge(['class' => 'sl-prof-item']) }}>
    <div
        class="sl-prof-item__ico"
        style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }};"
        aria-hidden="true"
    >
        {{ $document->fileKindLabel() }}
    </div>
    <div class="min-w-0 flex-1">
        <div class="mb-1 flex flex-wrap gap-1.5">
            <x-ui.badge :variant="$document->type->badgeVariant()">{{ $document->type->label() }}</x-ui.badge>
            @if ($document->module)
                <x-ui.badge variant="neutral">{{ $document->module->name }}</x-ui.badge>
            @endif
            <x-ui.badge :variant="$statusBadge['variant']">{{ $statusBadge['label'] }}</x-ui.badge>
        </div>
        <h3 class="text-h4 leading-snug font-semibold">{{ $document->title }}</h3>
        <div class="mt-1.5 flex flex-wrap items-center gap-4 text-sm text-muted">
            @if ($document->status === \App\Enums\DocumentStatus::Approved)
                <span class="inline-flex items-center gap-1">
                    <x-ui.icon name="download" class="h-3.5 w-3.5" />
                    {{ $document->downloads_count }}
                </span>
                <x-ui.star-rating :value="$rating" />
            @else
                <span>En cours de modération</span>
            @endif
            <span>{{ $document->created_at?->diffForHumans() }}</span>
        </div>
    </div>
    <div class="flex shrink-0 gap-2">
        @if ($document->status === \App\Enums\DocumentStatus::Approved)
            <x-ui.button variant="ghost" size="sm" href="{{ route('documents.show', $document) }}" wire:navigate>
                Voir
            </x-ui.button>
        @endif
        <x-ui.button variant="secondary" size="sm" href="{{ route('documents.index', ['mine' => 1]) }}" wire:navigate>
            Gérer
        </x-ui.button>
    </div>
</article>

@props([
    'document',
])

@php
    $colors = $document->thumbColors();
    $initials = collect(preg_split('/\s+/', trim($document->author?->name ?? '')) ?: [])
        ->take(2)
        ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<div {{ $attributes->merge(['class' => 'sl-adm-queue-item']) }}>
    <div
        class="sl-adm-queue-item__ico"
        style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }};"
        aria-hidden="true"
    >
        {{ $document->fileKindLabel() }}
    </div>
    <div class="min-w-0 flex-1">
        <div class="text-sm font-semibold leading-snug">{{ $document->title }}</div>
        <div class="mt-0.5 text-xs text-muted">
            {{ $document->module?->name ?? 'Module' }}
            · par {{ $document->author?->name ?? 'Anonyme' }}
            · {{ $document->created_at?->diffForHumans() }}
        </div>
    </div>
    <div class="flex shrink-0 gap-1.5">
        <a
            href="{{ route('documents.show', $document) }}"
            wire:navigate
            class="sl-adm-mini-act sl-adm-mini-act--view"
            title="Aperçu"
            aria-label="Aperçu"
        >
            <x-ui.icon name="eye" class="h-[15px] w-[15px]" />
        </a>
        <button
            type="button"
            class="sl-adm-mini-act sl-adm-mini-act--ok"
            title="Approuver"
            aria-label="Approuver"
            wire:click="approve('{{ $document->id }}')"
            wire:loading.attr="disabled"
        >
            <x-ui.icon name="check" class="h-[15px] w-[15px]" />
        </button>
        <button
            type="button"
            class="sl-adm-mini-act sl-adm-mini-act--no"
            title="Refuser"
            aria-label="Refuser"
            wire:click="openRejectModal('{{ $document->id }}')"
        >
            <x-ui.icon name="x" class="h-[15px] w-[15px]" />
        </button>
    </div>
</div>

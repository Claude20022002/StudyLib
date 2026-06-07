@props([
    'document',
])

@php
    $colors = $document->thumbColors();
    $initials = collect(preg_split('/\s+/', trim($document->author?->name ?? '')) ?: [])
        ->take(2)
        ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    $statusBadge = match ($document->status) {
        \App\Enums\DocumentStatus::Pending => 'warning',
        \App\Enums\DocumentStatus::Approved => 'success',
        \App\Enums\DocumentStatus::Rejected => 'danger',
    };
@endphp

<tr {{ $attributes }}>
    <td>
        <div class="sl-file-cell">
            <span
                class="sl-file-ico"
                style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; height:40px;width:34px;font-size:9px;"
            >
                {{ $document->fileKindLabel() }}
            </span>
            <span>{{ $document->title }}</span>
        </div>
    </td>
    <td>{{ $document->module?->name ?? '—' }}</td>
    <td>
        <div class="flex items-center gap-2">
            <x-ui.avatar :initials="$initials !== '' ? $initials : '?'" class="!h-[26px] !w-[26px] !text-[10px]" />
            {{ $document->author?->name ?? '—' }}
        </div>
    </td>
    <td>{{ $document->type->label() }}</td>
    <td>{{ $document->created_at?->translatedFormat('d M Y') }}</td>
    <td>
        <x-ui.badge :variant="$statusBadge">{{ $document->status->label() }}</x-ui.badge>
    </td>
    <td>
        <div class="flex justify-end gap-1.5">
            <x-ui.button variant="ghost" size="xs" href="{{ route('documents.show', $document) }}" wire:navigate>
                Voir
            </x-ui.button>
            @if ($document->status === \App\Enums\DocumentStatus::Pending)
                <button
                    type="button"
                    class="sl-btn sl-btn--success sl-btn--xs"
                    wire:click="approve('{{ $document->id }}')"
                    wire:loading.attr="disabled"
                >
                    <x-ui.icon name="check" />
                    Approuver
                </button>
                <button
                    type="button"
                    class="sl-btn sl-btn--secondary sl-btn--xs"
                    wire:click="openRejectModal('{{ $document->id }}')"
                >
                    Refuser
                </button>
            @endif
        </div>
    </td>
</tr>

@props([
    'event',
])

<div class="sl-event">
    <div class="sl-event-date" aria-hidden="true">
        <div class="text-h4 leading-none font-bold">{{ $event->starts_at->format('d') }}</div>
        <div class="text-[10px] font-bold tracking-widest uppercase">{{ $event->starts_at->locale('fr')->isoFormat('MMM') }}</div>
    </div>
    <div class="min-w-0">
        <h4 class="text-sm leading-snug font-semibold">{{ $event->title }}</h4>
        <p class="mt-0.5 flex items-center gap-1 text-xs text-muted">
            <x-ui.icon name="clock" class="h-[11px] w-[11px]" />
            {{ $event->starts_at->format('H:i') }}
            @if ($event->location)
                · {{ $event->location }}
            @endif
        </p>
    </div>
</div>

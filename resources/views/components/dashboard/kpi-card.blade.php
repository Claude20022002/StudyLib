@props([
    'kpi' => [],
])

<article class="sl-kpi">
    <div class="sl-kpi-top">
        <div @class(['sl-kpi-ico', $kpi['icon_bg'] ?? 'bg-primary-soft text-primary'])>
            <x-ui.icon :name="$kpi['icon'] ?? 'file'" class="h-5 w-5" />
        </div>
        @if (! empty($kpi['trend']))
            <span @class(['text-xs font-semibold inline-flex items-center gap-0.5', ($kpi['trend_up'] ?? false) ? 'text-success-ink' : 'text-muted'])>
                @if ($kpi['trend_up'] ?? false)
                    <x-ui.icon name="trend-up" class="h-3 w-3" />
                @endif
                {{ $kpi['trend'] }}
            </span>
        @elseif (! empty($kpi['badge']))
            <x-ui.badge :variant="$kpi['badge_variant'] ?? 'neutral'">{{ $kpi['badge'] }}</x-ui.badge>
        @endif
    </div>
    <div class="sl-kpi-val">{{ $kpi['value'] ?? 0 }}</div>
    <div class="sl-kpi-label">{{ $kpi['label'] ?? '' }}</div>
</article>

@props([
    'href' => null,
    'type' => null,
    'title' => '',
])

@if ($href)
    <a href="{{ $href }}" wire:navigate {{ $attributes->merge(['class' => 'sl-card']) }}>
        @if (isset($thumb) || $type)
            <div class="sl-card-thumb">
                @if ($type)
                    <span class="sl-card-type">{{ $type }}</span>
                @endif
                {{ $thumb ?? '' }}
            </div>
        @endif
        <div class="sl-card-body">
            @if (isset($meta))
                <div class="sl-card-meta">{{ $meta }}</div>
            @endif
            @if ($title)
                <div class="sl-card-title">{{ $title }}</div>
            @endif
            {{ $slot }}
            @if (isset($foot))
                <div class="sl-card-foot">{{ $foot }}</div>
            @endif
        </div>
    </a>
@else
    <article {{ $attributes->merge(['class' => 'sl-card']) }}>
        @if (isset($thumb) || $type)
            <div class="sl-card-thumb">
                @if ($type)
                    <span class="sl-card-type">{{ $type }}</span>
                @endif
                {{ $thumb ?? '' }}
            </div>
        @endif
        <div class="sl-card-body">
            @if (isset($meta))
                <div class="sl-card-meta">{{ $meta }}</div>
            @endif
            @if ($title)
                <div class="sl-card-title">{{ $title }}</div>
            @endif
            {{ $slot }}
            @if (isset($foot))
                <div class="sl-card-foot">{{ $foot }}</div>
            @endif
        </div>
    </article>
@endif

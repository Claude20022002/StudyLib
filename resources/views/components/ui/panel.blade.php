@props([
    'title' => '',
    'actionLabel' => null,
    'actionHref' => null,
])

<section {{ $attributes->merge(['class' => 'sl-panel']) }}>
    @if ($title || $actionLabel)
        <div class="sl-panel-head">
            @if ($title)
                <h3>{{ $title }}</h3>
            @endif
            @if ($actionLabel && $actionHref)
                <a href="{{ $actionHref }}">{{ $actionLabel }}</a>
            @endif
        </div>
    @endif
    <div class="sl-panel-body">
        {{ $slot }}
    </div>
</section>

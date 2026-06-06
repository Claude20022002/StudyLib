@if (isset($thumb) || isset($type) && $type)
    <div class="sl-card-thumb">
        @if (! empty($type))
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

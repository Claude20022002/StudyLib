@props([
    'title' => '',
    'description' => '',
    'flaticon' => null,
])

<div {{ $attributes->merge(['class' => 'sl-empty']) }}>
    @if ($flaticon)
        <div class="sl-empty-ico sl-empty-ico--flaticon">
            <x-ui.flaticon :name="$flaticon" class="sl-flaticon--empty" />
        </div>
    @elseif (isset($icon))
        <div class="sl-empty-ico">{{ $icon }}</div>
    @endif
    @if ($title)
        <h3>{{ $title }}</h3>
    @endif
    @if ($description)
        <p>{{ $description }}</p>
    @endif
    {{ $slot }}
</div>

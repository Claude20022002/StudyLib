@props([
    'title' => '',
    'description' => '',
])

<div {{ $attributes->merge(['class' => 'sl-empty']) }}>
    @if (isset($icon))
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

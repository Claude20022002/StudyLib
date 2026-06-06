@props([
    'href' => null,
    'showMark' => true,
])

<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    @if ($showMark)
        <div class="sl-brand-mark sl-brand-mark--lg" aria-hidden="true">S</div>
    @endif
    <span class="sl-brand-name">StudyLib</span>
    {{ $slot }}
</div>

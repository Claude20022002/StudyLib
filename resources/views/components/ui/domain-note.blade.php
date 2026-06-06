<div {{ $attributes->merge(['class' => 'sl-domain-note']) }} role="note">
    <x-ui.icon name="shield-check" class="h-[18px] w-[18px] shrink-0 text-primary" />
    <span>{{ $slot }}</span>
</div>

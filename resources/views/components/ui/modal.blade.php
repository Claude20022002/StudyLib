@props([
    'name' => 'modal',
    'title' => '',
    'description' => '',
])

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}' || $event.detail === null) open = false"
    x-on:keydown.escape.window="open = false"
    {{ $attributes }}
>
    <template x-teleport="body">
        <div x-show="open" x-cloak>
            <div class="sl-modal-scrim" x-on:click="open = false" aria-hidden="true"></div>
            <div
                class="sl-modal"
                role="dialog"
                aria-modal="true"
                @if ($title) aria-labelledby="{{ $name }}-title" @endif
            >
                @if ($title || $description)
                    <div class="sl-modal-head">
                        @if ($title)
                            <div class="sl-modal-title" id="{{ $name }}-title">{{ $title }}</div>
                        @endif
                        @if ($description)
                            <p class="sl-modal-desc">{{ $description }}</p>
                        @endif
                    </div>
                @endif

                @if (isset($head))
                    <div class="sl-modal-head">{{ $head }}</div>
                @endif

                <div class="sl-modal-body">
                    {{ $slot }}
                </div>

                @if (isset($foot))
                    <div class="sl-modal-foot">{{ $foot }}</div>
                @endif
            </div>
        </div>
    </template>
</div>

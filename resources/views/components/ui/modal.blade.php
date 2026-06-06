@props([
    'name' => 'modal',
    'title' => '',
    'description' => '',
])

<div
    x-data="{
        open: false,
        returnFocus: null,
        openModal(detail) {
            if (detail !== '{{ $name }}') {
                return;
            }
            this.returnFocus = document.activeElement;
            this.open = true;
            this.$nextTick(() => this.$refs.dialog?.focus());
        },
        closeModal() {
            if (! this.open) {
                return;
            }
            this.open = false;
            this.$nextTick(() => this.returnFocus?.focus?.());
        },
        trapTab(event) {
            if (! this.open) {
                return;
            }
            const focusable = this.$refs.dialog?.querySelectorAll(
                'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex=\'-1\'])',
            );
            if (! focusable?.length) {
                return;
            }
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (! event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
    }"
    x-on:open-modal.window="openModal($event.detail)"
    x-on:close-modal.window="if (! $event.detail || $event.detail === '{{ $name }}') closeModal()"
    x-on:keydown.escape.window="closeModal()"
    x-on:keydown.tab.window="trapTab($event)"
    {{ $attributes }}
>
    <template x-teleport="body">
        <div x-show="open" x-cloak>
            <div class="sl-modal-scrim" x-on:click="closeModal()" aria-hidden="true"></div>
            <div
                x-ref="dialog"
                class="sl-modal"
                role="dialog"
                aria-modal="true"
                tabindex="-1"
                @if ($title) aria-labelledby="{{ $name }}-title" @endif
                @if ($description) aria-describedby="{{ $name }}-desc" @endif
            >
                <div class="sl-modal-head">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            @if ($title)
                                <div class="sl-modal-title" id="{{ $name }}-title">{{ $title }}</div>
                            @endif
                            @if ($description)
                                <p class="sl-modal-desc" id="{{ $name }}-desc">{{ $description }}</p>
                            @endif
                            @isset($head)
                                {{ $head }}
                            @endisset
                        </div>
                        <button
                            type="button"
                            class="sl-modal-close"
                            x-on:click="closeModal()"
                            aria-label="Fermer la fenêtre"
                        >
                            <x-ui.icon name="x" class="h-4 w-4" />
                        </button>
                    </div>
                </div>

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

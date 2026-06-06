<div>
    <nav class="sl-bottom-nav" aria-label="Navigation mobile">
        <div class="sl-bottom-nav-inner">
            @foreach ($items as $item)
                @php
                    $href = $this->hrefFor($item);
                    $active = $this->isActive($item['route'] ?? null);
                @endphp
                @if ($href)
                    <a
                        href="{{ $href }}"
                        wire:navigate
                        @class(['sl-bottom-link', 'is-active' => $active])
                        @if($active) aria-current="page" @endif
                    >
                        <x-ui.icon :name="$item['icon']" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </nav>

    <button type="button" class="sl-fab" aria-label="Déposer">
        <x-ui.icon name="plus" class="h-6 w-6 text-white" />
    </button>
</div>

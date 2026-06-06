<aside class="sl-sidebar" aria-label="Navigation principale">
    <div class="flex items-center gap-3 px-5 py-5">
        <x-ui.brand />
    </div>

    <nav class="sl-sidebar-nav">
        @foreach ($mainNav as $item)
            @php
                $href = $this->hrefFor($item);
                $active = $this->isActive($item['route'] ?? null, $item['query'] ?? []);
                $count = $counts[$item['route'] ?? ''] ?? null;
            @endphp
            @if ($href)
                <a
                    href="{{ $href }}"
                    wire:navigate
                    @class(['sl-sidebar-link', 'is-active' => $active])
                    @if($active) aria-current="page" @endif
                >
                    <x-ui.icon :name="$item['icon']" />
                    <span>{{ $item['label'] }}</span>
                    @if ($count !== null)
                        <span class="sl-sidebar-count">{{ $count }}</span>
                    @endif
                </a>
            @endif
        @endforeach

        <div class="sl-sidebar-group">Mon espace</div>

        @foreach ($personalNav as $item)
            @php
                $href = $this->hrefFor($item);
                $active = $this->isActive($item['route'] ?? null, $item['query'] ?? []);
                $count = $counts[$item['route'] ?? ''] ?? null;
            @endphp
            @if (! empty($item['disabled']))
                <span class="sl-sidebar-link opacity-50" aria-disabled="true">
                    <x-ui.icon :name="$item['icon']" />
                    <span>{{ $item['label'] }}</span>
                </span>
            @elseif ($href)
                <a
                    href="{{ $href }}"
                    wire:navigate
                    @class(['sl-sidebar-link', 'is-active' => $active])
                    @if($active) aria-current="page" @endif
                >
                    <x-ui.icon :name="$item['icon']" />
                    <span>{{ $item['label'] }}</span>
                    @if ($count !== null)
                        <span class="sl-sidebar-count">{{ $count }}</span>
                    @endif
                </a>
            @endif
        @endforeach
    </nav>

    <div class="border-t border-border p-3">
        <div class="sl-sidebar-promo">
            <h4 class="mb-1 text-sm font-semibold">Partagez vos ressources</h4>
            <p class="mb-3 text-xs text-muted">Aidez votre promo en déposant vos cours et fiches.</p>
            <x-ui.button variant="primary" size="sm" class="w-full" href="{{ route('documents.index', ['upload' => 1]) }}" wire:navigate>
                <x-ui.icon name="plus" />
                Déposer un document
            </x-ui.button>
        </div>
    </div>
</aside>

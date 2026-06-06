<aside class="sl-admin-sidebar" aria-label="Navigation administration">
    <div class="flex flex-col gap-0.5 px-5 pt-5 pb-4">
        <div class="flex items-center gap-3">
            <div class="sl-brand-mark" style="width:34px;height:34px;font-size:16px;border-radius:10px" aria-hidden="true">S</div>
            <div>
                <div class="sl-brand-name">StudyLib</div>
                <div class="text-[10px] font-semibold tracking-widest text-slate-500 uppercase">Administration</div>
            </div>
        </div>
    </div>

    <nav class="flex flex-1 flex-col gap-0.5 overflow-y-auto px-3">
        @foreach ($items as $item)
            @php
                $routeName = $item['route'] ?? null;
                $active = $this->isActive($routeName);
            @endphp
            @if ($routeName && Route::has($routeName))
                <a
                    href="{{ route($routeName) }}"
                    wire:navigate
                    @class(['sl-sidebar-link', 'is-active' => $active])
                    @if($active) aria-current="page" @endif
                >
                    <x-ui.icon :name="$item['icon']" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>

    @if ($user)
        <div class="border-t border-white/10 p-3">
            <div class="flex items-center gap-3 p-2">
                <x-ui.avatar :initials="$initials" class="!bg-primary !text-white" />
                <div>
                    <div class="text-sm font-semibold text-white">{{ $user->name }}</div>
                    <div class="text-[11px] text-slate-500">Administrateur</div>
                </div>
            </div>
        </div>
    @endif
</aside>

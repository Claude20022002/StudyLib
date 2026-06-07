<header class="sl-topbar">
    <div class="sl-brand-mark sl-mobile-only" aria-hidden="true">S</div>

    @if ($breadcrumb)
        <nav class="sl-breadcrumb min-w-0" aria-label="Fil d'Ariane">
            @foreach ($breadcrumb as $index => $item)
                @if ($index > 0)
                    <span class="sl-breadcrumb__sep" aria-hidden="true">/</span>
                @endif
                @if (! empty($item['url']))
                    <a href="{{ $item['url'] }}" wire:navigate class="sl-breadcrumb__link">{{ $item['label'] }}</a>
                @else
                    <span class="sl-breadcrumb__current">{{ $item['label'] }}</span>
                @endif
            @endforeach
        </nav>
    @elseif ($title)
        <h1 class="text-h3 font-bold tracking-tight">{{ $title }}</h1>
    @else
        <livewire:ui.search-bar class="sl-desktop-only flex-1" />
    @endif

    <div class="flex-1"></div>

    <button type="button" class="sl-icon-btn sl-mobile-only" aria-label="Rechercher">
        <x-ui.icon name="search" class="h-5 w-5" />
    </button>

    <livewire:ui.notification-bell />

    @if ($user)
        <a href="{{ route('profile.show') }}" wire:navigate class="sl-user-chip" aria-label="Profil">
            <div class="sl-desktop-only text-right">
                <div class="text-sm font-semibold">{{ $user->name }}</div>
                <div class="text-xs text-muted">
                    {{ $user->filiere?->name }}
                    @if ($user->year_level)
                        · L{{ $user->year_level }}
                    @endif
                </div>
            </div>
            <x-ui.avatar :initials="$initials" />
        </a>
    @endif
</header>

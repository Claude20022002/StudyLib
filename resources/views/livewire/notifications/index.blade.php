<div>
    <section class="sl-notif-hero" aria-label="Centre de notifications">
        <div class="sl-notif-hero__ico" aria-hidden="true">
            <x-ui.flaticon name="bell" class="sl-flaticon--hero" />
        </div>
        <div class="min-w-0 flex-1">
            <h2 class="text-h4 leading-tight font-bold tracking-tight">Vos notifications</h2>
            <p class="mt-1 text-sm text-muted">
                @if ($unreadCount > 0)
                    {{ $unreadCount }} non lue{{ $unreadCount > 1 ? 's' : '' }}
                @else
                    Tout est à jour
                @endif
            </p>
        </div>
        @if ($unreadCount > 0)
            <button type="button" wire:click="markAllAsRead" class="sl-btn sl-btn--secondary">
                Tout marquer comme lu
            </button>
        @endif
    </section>

    <div wire:loading.class="opacity-60">
        @if ($notifications->isEmpty())
            <x-ui.empty-state
                flaticon="bell"
                title="Aucune notification"
                description="Les alertes de modération, d'événements et de rappels apparaîtront ici."
            />
        @else
            <ul class="sl-notif-list" aria-label="Liste des notifications">
                @foreach ($notifications as $notification)
                    @php
                        $isUnread = $notification->read_at === null;
                        $iconKey = $notificationService->iconKey($notification);
                    @endphp
                    <li wire:key="notif-{{ $notification->id }}">
                        <button
                            type="button"
                            wire:click="markAsRead('{{ $notification->id }}')"
                            @class(['sl-notif-item', 'is-unread' => $isUnread])
                        >
                            <span @class(['sl-notif-item__ico', 'sl-notif-item__ico--'.$iconKey])>
                                <x-ui.flaticon :name="$iconKey" class="sl-flaticon--notif" />
                            </span>
                            <span class="sl-notif-item__body">
                                <span class="sl-notif-item__title">{{ $notificationService->title($notification) }}</span>
                                <span class="sl-notif-item__text">{{ $notificationService->body($notification) }}</span>
                                <span class="sl-notif-item__time">{{ $notification->created_at?->locale('fr')->diffForHumans() }}</span>
                            </span>
                            @if ($isUnread)
                                <span class="sl-notif-item__dot" aria-hidden="true"></span>
                            @endif
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

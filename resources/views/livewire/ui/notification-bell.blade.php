<a
    href="{{ route('notifications.index') }}"
    wire:navigate
    class="sl-icon-btn"
    aria-label="{{ $unreadCount > 0 ? $unreadCount.' notifications non lues' : 'Notifications' }}"
>
    <x-ui.icon name="bell" class="h-5 w-5" />
    @if ($unreadCount > 0)
        <span class="sl-notif-dot" aria-hidden="true"></span>
    @endif
</a>

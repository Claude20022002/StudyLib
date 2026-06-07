<?php

declare(strict_types=1);

namespace App\Livewire\Notifications;

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public function markAsRead(string $notificationId, NotificationService $notifications): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $notification = Notification::query()->findOrFail($notificationId);
        abort_unless($notification->user_id === $user->getKey(), 403);

        $notifications->markAsRead($notification);
    }

    public function markAllAsRead(NotificationService $notifications): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $notifications->markAllAsRead($user->getKey());
    }

    public function render(NotificationService $notifications): View
    {
        $user = Auth::user();
        $userId = $user instanceof User ? $user->getKey() : '';

        return view('livewire.notifications.index', [
            'notifications' => $userId !== '' ? $notifications->listFor($userId) : collect(),
            'unreadCount' => $userId !== '' ? $notifications->unreadCount($userId) : 0,
            'notificationService' => $notifications,
        ]);
    }
}

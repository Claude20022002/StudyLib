<?php

declare(strict_types=1);

namespace App\Livewire\Ui;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;

    public function mount(NotificationService $notifications): void
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $this->unreadCount = $notifications->unreadCount($user->getKey());
        }
    }

    public function render(): View
    {
        return view('livewire.ui.notification-bell');
    }
}

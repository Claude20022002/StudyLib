<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends RepositoryInterface<Notification>
 */
interface NotificationRepositoryInterface extends RepositoryInterface
{
    /** @return Collection<int, Notification> */
    public function unreadForUser(string $userId): Collection;

    public function markAsRead(Notification $notification): void;

    public function unreadCountForUser(string $userId): int;
}

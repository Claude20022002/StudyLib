<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
    ) {
    }

    /** @return Collection<int, Notification> */
    public function unreadFor(string $userId): Collection
    {
        return $this->notifications->unreadForUser($userId);
    }

    public function markAsRead(Notification $notification): void
    {
        $this->notifications->markAsRead($notification);
    }

    public function notifyDocumentReviewed(Document $document, ?string $reason = null): ?Notification
    {
        if ($document->user_id === null) {
            return null;
        }

        return $this->notifications->create([
            'type' => 'document.reviewed',
            'user_id' => $document->user_id,
            'data' => [
                'document_id' => $document->getKey(),
                'title' => $document->title,
                'status' => $document->status->value,
                'reason' => $reason,
            ],
        ]);
    }
}

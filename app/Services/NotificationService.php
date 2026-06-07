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
    ) {}

    /** @return Collection<int, Notification> */
    public function unreadFor(string $userId): Collection
    {
        return $this->notifications->unreadForUser($userId);
    }

    /** @return Collection<int, Notification> */
    public function listFor(string $userId, int $limit = 50): Collection
    {
        return $this->notifications->listForUser($userId, $limit);
    }

    public function unreadCount(string $userId): int
    {
        return $this->notifications->unreadCountForUser($userId);
    }

    public function markAsRead(Notification $notification): void
    {
        $this->notifications->markAsRead($notification);
    }

    public function markAllAsRead(string $userId): void
    {
        $this->notifications->markAllAsReadForUser($userId);
    }

    public function title(Notification $notification): string
    {
        $data = $notification->data ?? [];

        return match ($notification->type) {
            'document.reviewed' => match ($data['status'] ?? null) {
                'approved' => 'Document approuvé',
                'rejected' => 'Document refusé',
                default => 'Mise à jour de modération',
            },
            default => 'Notification',
        };
    }

    public function body(Notification $notification): string
    {
        $data = $notification->data ?? [];
        $title = $data['title'] ?? 'Votre document';

        return match ($notification->type) {
            'document.reviewed' => match ($data['status'] ?? null) {
                'approved' => '« '.$title.' » est maintenant visible dans la bibliothèque.',
                'rejected' => filled($data['reason'] ?? null)
                    ? '« '.$title.' » a été refusé : '.$data['reason']
                    : '« '.$title.' » a été refusé par la modération.',
                default => '« '.$title.' » a été traité par la modération.',
            },
            default => 'Vous avez une nouvelle notification.',
        };
    }

    public function iconKey(Notification $notification): string
    {
        return match ($notification->type) {
            'document.reviewed' => match ($notification->data['status'] ?? null) {
                'approved' => 'library',
                'rejected' => 'empty',
                default => 'shield',
            },
            default => 'bell',
        };
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

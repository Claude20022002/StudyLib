<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

class ModerationService
{
    public function __construct(
        private readonly DocumentRepositoryInterface $documents,
        private readonly NotificationService $notifications,
    ) {}

    public function queue(): LengthAwarePaginator
    {
        return $this->documents->pendingModeration();
    }

    /**
     * @return array{all: int, pending: int, approved: int, rejected: int}
     */
    public function statusCounts(): array
    {
        return $this->documents->adminStatusCounts();
    }

    public function listForAdmin(string $statusFilter = 'pending', ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $status = match ($statusFilter) {
            'all' => null,
            'pending' => DocumentStatus::Pending,
            'approved' => DocumentStatus::Approved,
            'rejected' => DocumentStatus::Rejected,
            default => DocumentStatus::Pending,
        };

        return $this->documents->adminList($status, $search, $perPage);
    }

    public function approve(Document $document): Document
    {
        $this->ensurePending($document);

        $document = $this->documents->update($document, ['status' => DocumentStatus::Approved->value]);
        $this->notifications->notifyDocumentReviewed($document);

        return $document;
    }

    public function reject(Document $document, ?string $reason = null): Document
    {
        $this->ensurePending($document);

        $document = $this->documents->update($document, ['status' => DocumentStatus::Rejected->value]);
        $this->notifications->notifyDocumentReviewed($document, $reason);

        return $document;
    }

    private function ensurePending(Document $document): void
    {
        if ($document->status !== DocumentStatus::Pending) {
            throw new InvalidArgumentException('Seuls les documents en attente peuvent être modérés.');
        }
    }
}

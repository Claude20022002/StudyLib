<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ModerationService
{
    public function __construct(
        private readonly DocumentRepositoryInterface $documents,
        private readonly NotificationService $notifications,
    ) {
    }

    public function queue(): LengthAwarePaginator
    {
        return $this->documents->pendingModeration();
    }

    public function approve(Document $document): Document
    {
        $document = $this->documents->update($document, ['status' => DocumentStatus::Approved->value]);
        $this->notifications->notifyDocumentReviewed($document);

        return $document;
    }

    public function reject(Document $document, ?string $reason = null): Document
    {
        $document = $this->documents->update($document, ['status' => DocumentStatus::Rejected->value]);
        $this->notifications->notifyDocumentReviewed($document, $reason);

        return $document;
    }
}

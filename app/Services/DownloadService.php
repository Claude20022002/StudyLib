<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use App\Repositories\Contracts\DocumentDownloadRepositoryInterface;
use App\Repositories\Contracts\DocumentRepositoryInterface;

class DownloadService
{
    public function __construct(
        private readonly DocumentDownloadRepositoryInterface $downloads,
        private readonly DocumentRepositoryInterface $documents,
    ) {}

    /**
     * Trace un téléchargement et incrémente le compteur dénormalisé.
     */
    public function record(Document $document, ?string $userId, ?string $ip, ?string $userAgent): void
    {
        $this->downloads->create([
            'user_id' => $userId,
            'document_id' => $document->getKey(),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);

        $this->documents->incrementDownloads($document);
    }
}

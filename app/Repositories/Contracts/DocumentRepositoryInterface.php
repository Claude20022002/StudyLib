<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\DocumentType;
use App\Models\Document;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<Document>
 */
interface DocumentRepositoryInterface extends RepositoryInterface
{
    /** @return LengthAwarePaginator<int, Document> */
    public function listByModule(string $moduleId, ?DocumentType $type = null, int $perPage = 15): LengthAwarePaginator;

    /** @return LengthAwarePaginator<int, Document> */
    public function pendingModeration(int $perPage = 15): LengthAwarePaginator;

    public function incrementDownloads(Document $document): void;

    public function syncRatingAggregates(Document $document): void;
}

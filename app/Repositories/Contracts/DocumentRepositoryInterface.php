<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\DocumentType;
use App\Models\Document;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Carbon\CarbonInterface;

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

    /** @return Collection<int, Document> */
    public function recommendedForFiliere(string $filiereId, ?DocumentType $type = null, int $limit = 10): Collection;

    public function countApprovedSince(CarbonInterface $since, ?string $filiereId = null, ?CarbonInterface $until = null): int;

    public function countVisibleByType(DocumentType $type, ?string $filiereId = null): int;

    public function countVisibleByTypeSince(DocumentType $type, CarbonInterface $since, ?string $filiereId = null): int;
}

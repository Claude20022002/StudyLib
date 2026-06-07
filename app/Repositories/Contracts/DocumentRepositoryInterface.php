<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\DocumentType;
use App\Models\Document;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

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

    /**
     * @param  array{
     *     q?: string,
     *     filiere_id?: string,
     *     semester?: int,
     *     module_id?: string,
     *     year_concern?: int,
     *     types?: list<string>,
     *     min_rating?: float,
     *     sort?: string,
     *     mine?: bool,
     *     user_id?: string,
     * }  $filters
     * @return LengthAwarePaginator<int, Document>
     */
    public function browse(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * @param  array{
     *     q?: string,
     *     filiere_id?: string,
     *     semester?: int,
     *     module_id?: string,
     *     year_concern?: int,
     *     min_rating?: float,
     *     mine?: bool,
     *     user_id?: string,
     * }  $filters
     * @return array<string, int>
     */
    public function countByTypeForBrowse(array $filters): array;

    /** @return Collection<int, Document> */
    public function similarInModule(Document $document, int $limit = 3): Collection;

    /** @return Collection<int, Document> */
    public function examsInModule(Document $document, int $limit = 2): Collection;

    public function countApprovedByAuthor(string $userId): int;
}

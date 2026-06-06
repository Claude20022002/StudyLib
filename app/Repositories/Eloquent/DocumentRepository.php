<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Carbon\CarbonInterface;

/**
 * @extends BaseRepository<Document>
 */
class DocumentRepository extends BaseRepository implements DocumentRepositoryInterface
{
    public function __construct(Document $model)
    {
        parent::__construct($model);
    }

    public function listByModule(string $moduleId, ?DocumentType $type = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->visible()
            ->where('module_id', $moduleId)
            ->when($type, fn ($query) => $query->where('type', $type))
            ->latest()
            ->paginate($perPage);
    }

    public function pendingModeration(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->where('status', DocumentStatus::Pending)
            ->oldest()
            ->paginate($perPage);
    }

    public function incrementDownloads(Document $document): void
    {
        $document->newQuery()->whereKey($document->getKey())->increment('downloads_count');
    }

    public function syncRatingAggregates(Document $document): void
    {
        $aggregates = $document->ratings()
            ->selectRaw('COUNT(*) as count, COALESCE(AVG(score), 0) as average')
            ->first();

        $document->forceFill([
            'ratings_count' => (int) ($aggregates->count ?? 0),
            'avg_rating' => round((float) ($aggregates->average ?? 0), 1),
        ])->save();
    }

    public function recommendedForFiliere(string $filiereId, ?DocumentType $type = null, int $limit = 10): Collection
    {
        return $this->model->newQuery()
            ->visible()
            ->whereHas('module', fn ($query) => $query->where('filiere_id', $filiereId))
            ->when($type, fn ($query) => $query->where('type', $type))
            ->with(['author', 'module'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function countApprovedSince(CarbonInterface $since, ?string $filiereId = null, ?CarbonInterface $until = null): int
    {
        return $this->model->newQuery()
            ->visible()
            ->when($filiereId, fn ($query) => $query->whereHas(
                'module',
                fn ($moduleQuery) => $moduleQuery->where('filiere_id', $filiereId),
            ))
            ->where('created_at', '>=', $since)
            ->when($until, fn ($query) => $query->where('created_at', '<', $until))
            ->count();
    }

    public function countVisibleByType(DocumentType $type, ?string $filiereId = null): int
    {
        return $this->model->newQuery()
            ->visible()
            ->where('type', $type)
            ->when($filiereId, fn ($query) => $query->whereHas(
                'module',
                fn ($moduleQuery) => $moduleQuery->where('filiere_id', $filiereId),
            ))
            ->count();
    }

    public function countVisibleByTypeSince(DocumentType $type, Carbon $since, ?string $filiereId = null): int
    {
        return $this->model->newQuery()
            ->visible()
            ->where('type', $type)
            ->where('created_at', '>=', $since)
            ->when($filiereId, fn ($query) => $query->whereHas(
                'module',
                fn ($moduleQuery) => $moduleQuery->where('filiere_id', $filiereId),
            ))
            ->count();
    }
}

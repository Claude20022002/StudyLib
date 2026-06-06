<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
}

<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
            ->with(['author', 'module'])
            ->latest()
            ->paginate($perPage);
    }

    public function pendingModeration(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->where('status', DocumentStatus::Pending)
            ->with(['author', 'module'])
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

    public function countVisibleByTypeSince(DocumentType $type, CarbonInterface $since, ?string $filiereId = null): int
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

    public function browse(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->browseQuery($filters)
            ->with(['author', 'module.filiere']);

        $sort = $filters['sort'] ?? 'recent';

        if ($sort === 'popular') {
            $query->orderByDesc('downloads_count')->latest();
        } else {
            $query->latest();
        }

        return $query->paginate($perPage);
    }

    public function countByTypeForBrowse(array $filters): array
    {
        $counts = [];

        foreach (DocumentType::cases() as $type) {
            $counts[$type->value] = $this->browseQuery($filters)
                ->where('type', $type)
                ->count();
        }

        return $counts;
    }

    /**
     * @param  array{
     *     q?: string,
     *     filiere_id?: string,
     *     semester?: int,
     *     module_id?: string,
     *     year_concern?: int,
     *     types?: list<string>,
     *     min_rating?: float,
     *     mine?: bool,
     *     user_id?: string,
     * }  $filters
     */
    private function browseQuery(array $filters): Builder
    {
        $mine = (bool) ($filters['mine'] ?? false);
        $userId = $filters['user_id'] ?? null;

        $query = $this->model->newQuery();

        if ($mine && is_string($userId) && $userId !== '') {
            $query->where('user_id', $userId);
        } else {
            $query->visible();
        }

        if (! empty($filters['q'])) {
            $term = '%'.$filters['q'].'%';
            $query->where(function ($builder) use ($term) {
                $builder->where('title', 'like', $term)
                    ->orWhereHas('module', fn ($module) => $module->where('name', 'like', $term))
                    ->orWhereHas('author', fn ($author) => $author->where('name', 'like', $term));
            });
        }

        if (! empty($filters['filiere_id'])) {
            $query->whereHas(
                'module',
                fn ($module) => $module->where('filiere_id', $filters['filiere_id']),
            );
        }

        if (! empty($filters['semester'])) {
            $query->whereHas(
                'module',
                fn ($module) => $module->where('semester', (int) $filters['semester']),
            );
        }

        if (! empty($filters['module_id'])) {
            $query->where('module_id', $filters['module_id']);
        }

        if (! empty($filters['year_concern'])) {
            $query->where('year_concern', (int) $filters['year_concern']);
        }

        if (! empty($filters['types'])) {
            $query->whereIn('type', $filters['types']);
        }

        if (! empty($filters['min_rating'])) {
            $query->where('avg_rating', '>=', (float) $filters['min_rating']);
        }

        return $query;
    }
}

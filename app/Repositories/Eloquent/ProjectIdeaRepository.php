<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ProjectIdea;
use App\Repositories\Contracts\ProjectIdeaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepository<ProjectIdea>
 */
class ProjectIdeaRepository extends BaseRepository implements ProjectIdeaRepositoryInterface
{
    public function __construct(ProjectIdea $model)
    {
        parent::__construct($model);
    }

    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->when($filters['filiere_id'] ?? null, fn ($q, $v) => $q->where('filiere_id', $v))
            ->when($filters['level'] ?? null, fn ($q, $v) => $q->where('level', $v))
            ->when($filters['source'] ?? null, fn ($q, $v) => $q->where('source', $v))
            ->latest()
            ->paginate($perPage);
    }
}

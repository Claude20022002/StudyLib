<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ProjectIdea;
use App\Repositories\Contracts\ProjectIdeaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends BaseRepository<ProjectIdea>
 */
class ProjectIdeaRepository extends BaseRepository implements ProjectIdeaRepositoryInterface
{
    public function __construct(ProjectIdea $model)
    {
        parent::__construct($model);
    }

    public function search(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->searchQuery($filters)
            ->with(['user', 'filiere']);

        $sort = $filters['sort'] ?? 'recent';

        if ($sort === 'level') {
            $query->orderBy('level');
        } else {
            $query->latest();
        }

        return $query->paginate($perPage);
    }

    public function findWithRelations(string $id): ?ProjectIdea
    {
        return $this->model->newQuery()
            ->with(['user', 'filiere'])
            ->find($id);
    }

    /** @return Builder<ProjectIdea> */
    private function searchQuery(array $filters): Builder
    {
        $query = $this->model->newQuery();

        if (! empty($filters['q'])) {
            $term = '%'.$filters['q'].'%';
            $query->where(function ($builder) use ($term): void {
                $builder->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if (! empty($filters['filiere_id'])) {
            $query->where('filiere_id', $filters['filiere_id']);
        }

        if (! empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (! empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        return $query;
    }
}

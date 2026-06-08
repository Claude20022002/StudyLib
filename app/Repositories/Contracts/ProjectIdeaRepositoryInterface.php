<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\ProjectIdea;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends RepositoryInterface<ProjectIdea>
 */
interface ProjectIdeaRepositoryInterface extends RepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, ProjectIdea>
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function findWithRelations(string $id): ?ProjectIdea;

    /**
     * Idées de projet éligibles à la recommandation pour un étudiant donné
     * (hors propres idées de l'étudiant), avec les relations de matching pré-chargées.
     *
     * @return Collection<int, ProjectIdea>
     */
    public function candidatesForRecommendation(string $excludeUserId): Collection;
}

<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\ProjectIdea;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
}

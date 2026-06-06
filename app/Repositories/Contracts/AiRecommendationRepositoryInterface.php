<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\AiRecommendation;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends RepositoryInterface<AiRecommendation>
 */
interface AiRecommendationRepositoryInterface extends RepositoryInterface
{
    /** @return Collection<int, AiRecommendation> */
    public function forUser(string $userId): Collection;
}

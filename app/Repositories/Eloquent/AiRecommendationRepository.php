<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\AiRecommendation;
use App\Repositories\Contracts\AiRecommendationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<AiRecommendation>
 */
class AiRecommendationRepository extends BaseRepository implements AiRecommendationRepositoryInterface
{
    public function __construct(AiRecommendation $model)
    {
        parent::__construct($model);
    }

    public function forUser(string $userId): Collection
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->get();
    }
}

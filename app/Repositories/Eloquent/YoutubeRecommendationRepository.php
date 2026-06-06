<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\YoutubeRecommendation;
use App\Repositories\Contracts\YoutubeRecommendationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<YoutubeRecommendation>
 */
class YoutubeRecommendationRepository extends BaseRepository implements YoutubeRecommendationRepositoryInterface
{
    public function __construct(YoutubeRecommendation $model)
    {
        parent::__construct($model);
    }

    public function forModule(string $moduleId, int $limit = 10): Collection
    {
        return $this->model->newQuery()
            ->where('module_id', $moduleId)
            ->orderBy('position')
            ->limit($limit)
            ->get();
    }
}

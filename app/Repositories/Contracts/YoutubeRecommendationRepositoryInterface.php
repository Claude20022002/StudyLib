<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\YoutubeRecommendation;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends RepositoryInterface<YoutubeRecommendation>
 */
interface YoutubeRecommendationRepositoryInterface extends RepositoryInterface
{
    /** @return Collection<int, YoutubeRecommendation> */
    public function forModule(string $moduleId): Collection;
}

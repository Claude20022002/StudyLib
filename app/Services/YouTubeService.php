<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Module;
use App\Models\YoutubeRecommendation;
use App\Repositories\Contracts\YoutubeRecommendationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class YouTubeService
{
    private const CACHE_TTL_MINUTES = 1440;

    public function __construct(
        private readonly YoutubeRecommendationRepositoryInterface $recommendations,
    ) {
    }

    /** @return Collection<int, YoutubeRecommendation> */
    public function forModule(Module $module): Collection
    {
        return $this->recommendations->forModule($module->getKey());
    }

    /**
     * Récupère les vidéos depuis l'API YouTube (mises en cache pour limiter le quota).
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchFromApi(string $query): array
    {
        return Cache::remember(
            "youtube:search:{$query}",
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn (): array => Http::get('https://www.googleapis.com/youtube/v3/search', [
                'key' => config('services.youtube.key'),
                'q' => $query,
                'part' => 'snippet',
                'type' => 'video',
                'maxResults' => 6,
            ])->throw()->json('items', []),
        );
    }
}

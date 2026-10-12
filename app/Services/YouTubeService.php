<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Module;
use App\Models\YoutubeRecommendation;
use App\Repositories\Contracts\YoutubeRecommendationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class YouTubeService
{
    private const CACHE_TTL_MINUTES = 1440;

    public function __construct(
        private readonly YoutubeRecommendationRepositoryInterface $recommendations,
    ) {}

    /** @return Collection<int, YoutubeRecommendation> */
    public function forModule(Module $module): Collection
    {
        return $this->recommendations->forModule($module->getKey());
    }

    /**
     * Récupère les vidéos depuis l'API YouTube (mises en cache pour limiter le quota).
     * En cas de panne ou de délai dépassé : aucune vidéo, et rien n'est mis en cache.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchFromApi(string $query): array
    {
        try {
            return Cache::remember(
                "youtube:search:{$query}",
                now()->addMinutes(self::CACHE_TTL_MINUTES),
                fn (): array => Http::connectTimeout(5)->timeout(8)->get('https://www.googleapis.com/youtube/v3/search', [
                    'key' => config('services.youtube.key'),
                    'q' => $query,
                    'part' => 'snippet',
                    'type' => 'video',
                    'maxResults' => 6,
                ])->throw()->json('items', []),
            );
        } catch (ConnectionException|RequestException) {
            return [];
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts;
use App\Repositories\Eloquent;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Map des contrats vers leurs implémentations Eloquent.
     *
     * @var array<class-string, class-string>
     */
    private array $repositories = [
        Contracts\UserRepositoryInterface::class => Eloquent\UserRepository::class,
        Contracts\FiliereRepositoryInterface::class => Eloquent\FiliereRepository::class,
        Contracts\ModuleRepositoryInterface::class => Eloquent\ModuleRepository::class,
        Contracts\DocumentRepositoryInterface::class => Eloquent\DocumentRepository::class,
        Contracts\DocumentRatingRepositoryInterface::class => Eloquent\DocumentRatingRepository::class,
        Contracts\DocumentDownloadRepositoryInterface::class => Eloquent\DocumentDownloadRepository::class,
        Contracts\CompanyRepositoryInterface::class => Eloquent\CompanyRepository::class,
        Contracts\InternshipReviewRepositoryInterface::class => Eloquent\InternshipReviewRepository::class,
        Contracts\ProjectIdeaRepositoryInterface::class => Eloquent\ProjectIdeaRepository::class,
        Contracts\EventRepositoryInterface::class => Eloquent\EventRepository::class,
        Contracts\YoutubeRecommendationRepositoryInterface::class => Eloquent\YoutubeRecommendationRepository::class,
        Contracts\AiRecommendationRepositoryInterface::class => Eloquent\AiRecommendationRepository::class,
        Contracts\NotificationRepositoryInterface::class => Eloquent\NotificationRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->repositories as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\User;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\InternshipReviewRepositoryInterface;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\YoutubeRecommendationRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        private readonly DocumentRepositoryInterface $documents,
        private readonly EventRepositoryInterface $events,
        private readonly InternshipReviewRepositoryInterface $internshipReviews,
        private readonly NotificationRepositoryInterface $notifications,
        private readonly YoutubeRecommendationRepositoryInterface $youtube,
    ) {}

    /**
     * @return array{
     *     greeting_name: string,
     *     filiere_name: ?string,
     *     year_level: ?int,
     *     date_label: string,
     *     kpis: array<int, array<string, mixed>>,
     *     section_subtitle: string,
     *     profile_completion: int,
     *     internship_match_label: string,
     *     unread_notifications: int,
     * }
     */
    public function overview(User $user): array
    {
        $filiereId = $user->filiere_id;
        $filiereName = $user->filiere?->name;

        $weekStart = Carbon::now()->startOfWeek();
        $previousWeekStart = $weekStart->copy()->subWeek();

        $newThisWeek = $filiereId
            ? $this->documents->countApprovedSince($weekStart, $filiereId)
            : $this->documents->countApprovedSince($weekStart);
        $newPreviousWeek = $filiereId
            ? $this->documents->countApprovedSince($previousWeekStart, $filiereId, $weekStart)
            : $this->documents->countApprovedSince($previousWeekStart, null, $weekStart);
        $weeklyTrend = $newThisWeek - $newPreviousWeek;

        $examsTotal = $filiereId
            ? $this->documents->countVisibleByType(DocumentType::Examen, $filiereId)
            : $this->documents->countVisibleByType(DocumentType::Examen);
        $examsThisWeek = $filiereId
            ? $this->documents->countVisibleByTypeSince(DocumentType::Examen, $weekStart, $filiereId)
            : $this->documents->countVisibleByTypeSince(DocumentType::Examen, $weekStart);

        $internshipCount = $filiereId
            ? $this->internshipReviews->countForFiliere($filiereId)
            : 0;

        $eventsCount = $this->events->countUpcoming();
        $daysUntilEvent = $this->events->daysUntilNext();

        return [
            'greeting_name' => $this->firstName($user->name),
            'filiere_name' => $filiereName,
            'year_level' => $user->year_level,
            'date_label' => $this->formatDateLabel(Carbon::now()),
            'kpis' => [
                [
                    'value' => $newThisWeek,
                    'label' => 'Nouveaux documents cette semaine',
                    'icon' => 'file',
                    'icon_bg' => 'bg-primary-soft text-primary',
                    'trend' => $weeklyTrend > 0 ? '+'.$weeklyTrend : null,
                    'trend_up' => $weeklyTrend > 0,
                ],
                [
                    'value' => $examsTotal,
                    'label' => 'Examens disponibles',
                    'icon' => 'check',
                    'icon_bg' => 'bg-warning-soft text-warning',
                    'badge' => $examsThisWeek > 0 ? $examsThisWeek.' cette sem.' : null,
                    'badge_variant' => 'warning',
                ],
                [
                    'value' => $internshipCount,
                    'label' => 'Stages recommandés',
                    'icon' => 'briefcase',
                    'icon_bg' => 'bg-success-soft text-success',
                    'trend' => $internshipCount > 0 ? 'Match 92%' : null,
                    'trend_up' => true,
                ],
                [
                    'value' => $eventsCount,
                    'label' => 'Événements à venir',
                    'icon' => 'calendar',
                    'icon_bg' => 'bg-info-soft text-info',
                    'badge' => $daysUntilEvent !== null ? $daysUntilEvent.' j.' : null,
                    'badge_variant' => 'neutral',
                ],
            ],
            'section_subtitle' => trim(collect([$filiereName, $user->year_level ? 'L'.$user->year_level : null])->filter()->implode(' ')),
            'profile_completion' => $this->profileCompletion($user),
            'internship_match_label' => (string) min($internshipCount, 5),
            'unread_notifications' => $this->notifications->unreadCountForUser($user->getKey()),
        ];
    }

    /** @return Collection<int, \App\Models\Document> */
    public function recommendedDocuments(User $user, ?DocumentType $type = null, int $limit = 5): Collection
    {
        if ($user->filiere_id === null) {
            return collect();
        }

        return $this->documents->recommendedForFiliere($user->filiere_id, $type, $limit);
    }

    /** @return Collection<int, \App\Models\Event> */
    public function upcomingEvents(int $limit = 3): Collection
    {
        return $this->events->upcomingList($limit);
    }

    /** @return Collection<int, \App\Models\YoutubeRecommendation> */
    public function featuredVideos(User $user, int $limit = 2): Collection
    {
        $module = $user->filiere?->modules()->orderBy('semester')->first();

        if ($module === null) {
            return collect();
        }

        return $this->youtube->forModule($module->getKey(), $limit);
    }

    private function firstName(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];

        return $parts[0] ?? $fullName;
    }

    private function formatDateLabel(Carbon $date): string
    {
        return $date->locale('fr')->isoFormat('dddd D MMMM');
    }

    private function profileCompletion(User $user): int
    {
        $fields = [
            filled($user->name),
            filled($user->email),
            $user->filiere_id !== null,
            $user->year_level !== null,
            filled($user->avatar_path),
        ];

        return (int) round(array_sum($fields) / count($fields) * 100);
    }
}

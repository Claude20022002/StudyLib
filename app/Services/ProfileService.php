<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\InternshipReview;
use App\Models\ProjectIdea;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection as SupportCollection;

class ProfileService
{
    private const AVATAR_DISK = 'public';

    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * @return array{
     *     user: User,
     *     stats: array{
     *         documents_count: int,
     *         downloads_received: int,
     *         internship_reviews_count: int,
     *         project_ideas_count: int,
     *         avg_document_rating: float,
     *         ratings_received: int,
     *     },
     *     documents: Collection<int, Document>,
     *     internship_reviews: Collection<int, InternshipReview>,
     *     project_ideas: Collection<int, ProjectIdea>,
     *     recent_activity: SupportCollection<int, array{time: string, text: string, tone: string}>,
     * }
     */
    public function showPageData(User $user): array
    {
        $user->load('filiere');

        $documents = $user->documents()
            ->with('module')
            ->latest()
            ->limit(10)
            ->get();

        $approvedDocs = $user->documents()->where('status', DocumentStatus::Approved);

        return [
            'user' => $user,
            'stats' => [
                'documents_count' => $user->documents()->count(),
                'downloads_received' => (int) $user->documents()->sum('downloads_count'),
                'internship_reviews_count' => $user->internshipReviews()->count(),
                'project_ideas_count' => $user->projectIdeas()->count(),
                'avg_document_rating' => round((float) ($approvedDocs->avg('avg_rating') ?? 0), 1),
                'ratings_received' => (int) $approvedDocs->sum('ratings_count'),
            ],
            'documents' => $documents,
            'internship_reviews' => $user->internshipReviews()
                ->with('company')
                ->latest()
                ->limit(10)
                ->get(),
            'project_ideas' => $user->projectIdeas()
                ->latest()
                ->limit(10)
                ->get(),
            'recent_activity' => $this->recentActivity($user),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data, ?UploadedFile $avatar = null): User
    {
        if ($avatar !== null) {
            $data['avatar_path'] = $avatar->store('avatars/'.$user->getKey(), self::AVATAR_DISK);
        }

        unset($data['avatar']);

        return $this->users->update($user, $data);
    }

    /** @return SupportCollection<int, array{time: string, text: string, tone: string}> */
    private function recentActivity(User $user): SupportCollection
    {
        return $user->documents()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Document $document) => [
                'time' => $document->created_at?->diffForHumans() ?? '',
                'text' => 'Vous avez déposé « '.$document->title.' »'.($document->module ? ' en '.$document->module->name : '').'.',
                'tone' => 'primary',
            ]);
    }
}

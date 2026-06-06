<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentRating;
use App\Models\User;
use App\Repositories\Contracts\DocumentRatingRepositoryInterface;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Support\Facades\DB;

class RatingService
{
    public function __construct(
        private readonly DocumentRatingRepositoryInterface $ratings,
        private readonly DocumentRepositoryInterface $documents,
    ) {
    }

    /**
     * Crée ou met à jour la note d'un utilisateur, puis synchronise les agrégats du document.
     */
    public function rate(User $user, Document $document, int $score): DocumentRating
    {
        return DB::transaction(function () use ($user, $document, $score): DocumentRating {
            $existing = $this->ratings->findForUserAndDocument($user->getKey(), $document->getKey());

            $rating = $existing
                ? $this->ratings->update($existing, ['score' => $score])
                : $this->ratings->create([
                    'user_id' => $user->getKey(),
                    'document_id' => $document->getKey(),
                    'score' => $score,
                ]);

            $this->documents->syncRatingAggregates($document);

            return $rating;
        });
    }
}

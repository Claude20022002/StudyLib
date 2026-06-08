<?php

declare(strict_types=1);

namespace App\Services\Recommendation\Criteria;

use App\Models\ProjectIdea;
use App\Models\User;
use App\Services\Recommendation\Contracts\ProjectMatchCriterion;

/**
 * Affinité de filière : un projet rattaché à la filière de l'étudiant est le signal
 * le plus fort de pertinence pédagogique. Un projet sans filière déclarée est considéré
 * comme transverse (ouvert à tous) et reçoit un score neutre plutôt qu'une pénalité.
 */
final class FiliereMatchCriterion implements ProjectMatchCriterion
{
    private const WEIGHT = 0.30;

    private const NEUTRAL_SCORE = 0.5;

    public function weight(): float
    {
        return self::WEIGHT;
    }

    public function key(): string
    {
        return 'filiere';
    }

    public function score(User $student, ProjectIdea $project): float
    {
        if ($project->filiere_id === null) {
            return self::NEUTRAL_SCORE;
        }

        if ($student->filiere_id === null) {
            return self::NEUTRAL_SCORE;
        }

        return $student->filiere_id === $project->filiere_id ? 1.0 : 0.0;
    }
}

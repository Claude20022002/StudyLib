<?php

declare(strict_types=1);

namespace App\Services\Recommendation\Criteria;

use App\Enums\StudyLevel;
use App\Models\ProjectIdea;
use App\Models\User;
use App\Services\Recommendation\Contracts\ProjectMatchCriterion;

/**
 * Affinité de niveau d'études : un projet calibré pour le niveau exact de l'étudiant
 * (ex. L3 pour un étudiant en L3) est idéal. Un écart d'un niveau reste pertinent
 * (légèrement en dessous = consolidation, légèrement au-dessus = challenge accessible),
 * un écart plus important est jugé peu pertinent.
 */
final class LevelMatchCriterion implements ProjectMatchCriterion
{
    private const WEIGHT = 0.20;

    private const NEUTRAL_SCORE = 0.5;

    private const ADJACENT_LEVEL_SCORE = 0.5;

    public function weight(): float
    {
        return self::WEIGHT;
    }

    public function key(): string
    {
        return 'level';
    }

    public function score(User $student, ProjectIdea $project): float
    {
        if ($student->year_level === null) {
            return self::NEUTRAL_SCORE;
        }

        $studentLevel = StudyLevel::fromYearLevel($student->year_level);
        $distance = $studentLevel->distanceTo($project->level);

        return match (true) {
            $distance === 0 => 1.0,
            $distance === 1 => self::ADJACENT_LEVEL_SCORE,
            default => 0.0,
        };
    }
}

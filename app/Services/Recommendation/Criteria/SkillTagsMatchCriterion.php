<?php

declare(strict_types=1);

namespace App\Services\Recommendation\Criteria;

use App\Models\ProjectIdea;
use App\Models\User;
use App\Services\Recommendation\Contracts\ProjectMatchCriterion;

/**
 * Affinité de compétences : un projet dont les technologies requises recoupent
 * les compétences déclarées par l'étudiant (ex. Laravel, React, PostgreSQL) est
 * directement réalisable sans montée en compétence préalable. Le score mesure la
 * part des compétences requises par le projet déjà maîtrisées par l'étudiant.
 *
 * C'est volontairement le critère le plus lourdement pondéré : les tags de
 * compétences sont le signal le plus direct et le moins ambigu de faisabilité,
 * et ne nécessitent aucun appel à un modèle de langage pour être exploités.
 */
final class SkillTagsMatchCriterion implements ProjectMatchCriterion
{
    private const WEIGHT = 0.30;

    private const NEUTRAL_SCORE = 0.5;

    public function weight(): float
    {
        return self::WEIGHT;
    }

    public function key(): string
    {
        return 'tags';
    }

    public function score(User $student, ProjectIdea $project): float
    {
        $requiredTagIds = $project->tags->pluck('id');

        if ($requiredTagIds->isEmpty()) {
            return self::NEUTRAL_SCORE;
        }

        $studentTagIds = $student->tags->pluck('id');

        $covered = $requiredTagIds->intersect($studentTagIds)->count();

        return $covered / $requiredTagIds->count();
    }
}

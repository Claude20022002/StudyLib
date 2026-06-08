<?php

declare(strict_types=1);

namespace App\Services\Recommendation\Criteria;

use App\Models\ProjectIdea;
use App\Models\User;
use App\Services\Recommendation\Contracts\ProjectMatchCriterion;

/**
 * Affinité de modules suivis : un projet construit sur des modules que l'étudiant
 * a réellement suivis est immédiatement réalisable. Le score mesure la part des
 * modules requis par le projet que l'étudiant a déjà suivis (couverture des
 * prérequis), et non l'inverse — un étudiant qui suit beaucoup plus de modules
 * que nécessaire ne doit pas être pénalisé.
 */
final class FollowedModulesMatchCriterion implements ProjectMatchCriterion
{
    private const WEIGHT = 0.20;

    private const NEUTRAL_SCORE = 0.5;

    public function weight(): float
    {
        return self::WEIGHT;
    }

    public function key(): string
    {
        return 'modules';
    }

    public function score(User $student, ProjectIdea $project): float
    {
        $requiredModuleIds = $project->modules->pluck('id');

        if ($requiredModuleIds->isEmpty()) {
            return self::NEUTRAL_SCORE;
        }

        $followedModuleIds = $student->modules->pluck('id');

        $covered = $requiredModuleIds->intersect($followedModuleIds)->count();

        return $covered / $requiredModuleIds->count();
    }
}

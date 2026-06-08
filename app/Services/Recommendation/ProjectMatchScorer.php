<?php

declare(strict_types=1);

namespace App\Services\Recommendation;

use App\Models\ProjectIdea;
use App\Models\User;
use App\Services\Recommendation\Contracts\ProjectMatchCriterion;
use App\Services\Recommendation\Criteria\FiliereMatchCriterion;
use App\Services\Recommendation\Criteria\FollowedModulesMatchCriterion;
use App\Services\Recommendation\Criteria\LevelMatchCriterion;
use App\Services\Recommendation\Criteria\SkillTagsMatchCriterion;

/**
 * Agrège un ensemble de critères de matching indépendants en un score unique
 * normalisé sur 100, par moyenne pondérée des scores normalisés [0, 1] de chaque
 * critère.
 *
 * Le scorer ignore délibérément la nature de chaque critère (Dependency Inversion :
 * il dépend de l'interface `ProjectMatchCriterion`, jamais d'une implémentation
 * concrète) — ajouter un cinquième signal de matching ne nécessite aucune
 * modification de cette classe, seulement l'ajout du critère à la liste par défaut
 * (ou à une liste personnalisée injectée pour des besoins spécifiques, ex. A/B test).
 */
final class ProjectMatchScorer
{
    /**
     * @param  iterable<ProjectMatchCriterion>  $criteria
     */
    public function __construct(private readonly iterable $criteria) {}

    public function score(User $student, ProjectIdea $project): ProjectMatchScore
    {
        $weightedSum = 0.0;
        $totalWeight = 0.0;
        $breakdown = [];

        foreach ($this->criteria as $criterion) {
            $weight = $criterion->weight();
            $normalized = max(0.0, min(1.0, $criterion->score($student, $project)));

            $breakdown[$criterion->key()] = $normalized;
            $weightedSum += $weight * $normalized;
            $totalWeight += $weight;
        }

        $globalScore = $totalWeight > 0.0 ? ($weightedSum / $totalWeight) * 100 : 0.0;

        return new ProjectMatchScore($project, round($globalScore, 1), $breakdown);
    }

    /**
     * Composition par défaut du moteur, basée uniquement sur des règles métier
     * (filière, niveau, modules suivis, compétences) — aucun appel à un LLM.
     */
    public static function default(): self
    {
        return new self([
            new FiliereMatchCriterion,
            new LevelMatchCriterion,
            new FollowedModulesMatchCriterion,
            new SkillTagsMatchCriterion,
        ]);
    }
}

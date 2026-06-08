<?php

declare(strict_types=1);

namespace App\Services\Recommendation;

use App\Models\ProjectIdea;
use App\Models\User;
use App\Repositories\Contracts\ProjectIdeaRepositoryInterface;

/**
 * Point d'entrée unique du moteur de recommandation de projets CV.
 *
 * Respecte la même chaîne que le reste de StudyLib (Service → Repository → Model) :
 * cette classe orchestre la récupération des candidats et le scoring, sans jamais
 * accéder directement à Eloquent ni porter elle-même les règles de matching
 * (déléguées au `ProjectMatchScorer` et à ses critères).
 */
final class ProjectRecommendationService
{
    private const DEFAULT_LIMIT = 5;

    private const MINIMUM_SCORE = 30.0;

    public function __construct(
        private readonly ProjectIdeaRepositoryInterface $projects,
        private readonly ProjectMatchScorer $scorer,
    ) {}

    /**
     * Idées de projet les plus compatibles avec le profil de l'étudiant
     * (filière, niveau, modules suivis, compétences), classées par score décroissant.
     *
     * Les correspondances trop faibles (sous `MINIMUM_SCORE`) sont écartées :
     * mieux vaut une liste courte et pertinente qu'une liste complète diluée par
     * du bruit — un projet sans aucune affinité réelle ne devient pas "recommandé"
     * simplement pour compléter un quota.
     *
     * @return list<ProjectMatchScore>
     */
    public function recommendFor(User $student, int $limit = self::DEFAULT_LIMIT): array
    {
        $student->loadMissing(['modules', 'tags']);

        return $this->projects->candidatesForRecommendation($student->getKey())
            ->map(fn (ProjectIdea $project): ProjectMatchScore => $this->scorer->score($student, $project))
            ->filter(fn (ProjectMatchScore $match): bool => $match->score >= self::MINIMUM_SCORE)
            ->sortByDesc(fn (ProjectMatchScore $match): float => $match->score)
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * Score de compatibilité détaillé pour un couple étudiant/projet précis
     * (ex. affichage "pourquoi ce projet vous est recommandé" sur sa page de détail).
     */
    public function matchFor(User $student, ProjectIdea $project): ProjectMatchScore
    {
        $student->loadMissing(['modules', 'tags']);
        $project->loadMissing(['tags', 'modules']);

        return $this->scorer->score($student, $project);
    }
}

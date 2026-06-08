<?php

declare(strict_types=1);

namespace App\Services\Recommendation;

use App\Models\ProjectIdea;

/**
 * Résultat de matching pour une idée de projet : un score global agrégé (0-100)
 * accompagné du détail par critère, pour rester explicable ("pourquoi ce projet
 * vous est recommandé") sans recourir à un modèle de langage.
 */
final class ProjectMatchScore
{
    /**
     * @param  array<string, float>  $breakdown  clé du critère => score normalisé [0.0, 1.0]
     */
    public function __construct(
        public readonly ProjectIdea $project,
        public readonly float $score,
        public readonly array $breakdown,
    ) {}
}

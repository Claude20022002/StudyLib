<?php

declare(strict_types=1);

namespace App\Services\Recommendation\Contracts;

use App\Models\ProjectIdea;
use App\Models\User;

/**
 * Un critère de matching mesure une seule dimension d'affinité entre un étudiant
 * et une idée de projet, et renvoie un score normalisé indépendant des autres critères.
 *
 * Respecter cette interface (plutôt qu'une grosse fonction de scoring monolithique)
 * permet d'ajouter, retirer ou repondérer un critère sans toucher au reste du moteur
 * (Open/Closed) et de tester chaque règle métier isolément (Single Responsibility).
 */
interface ProjectMatchCriterion
{
    /**
     * Poids relatif du critère dans l'agrégation finale (valeur strictement positive).
     * Les poids n'ont pas besoin de sommer à 1 : le score agrégé est normalisé
     * par la somme des poids effectivement appliqués.
     */
    public function weight(): float;

    /**
     * Score d'affinité normalisé entre 0.0 (aucune affinité) et 1.0 (affinité parfaite).
     */
    public function score(User $student, ProjectIdea $project): float;

    /**
     * Clé courte identifiant le critère dans le détail du score (ex. affichage "pourquoi
     * ce projet vous est recommandé").
     */
    public function key(): string;
}

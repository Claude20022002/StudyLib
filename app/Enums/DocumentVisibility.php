<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Portée d'un document. Un document de classe n'existe que pour l'espace de cours de Planner,
 * qui en garde les droits (plan espace-de-cours.md, § 3) ; StudyLib ne le montre nulle part.
 */
enum DocumentVisibility: string
{
    case School = 'school';
    case ClassOnly = 'class';
}

<?php

declare(strict_types=1);

namespace App\Enums;

enum AiKind: string
{
    case Project = 'project';
    case Document = 'document';
    case StudyPath = 'study_path';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Project => 'Idée de projet',
            self::Document => 'Ressource',
            self::StudyPath => 'Parcours d\'étude',
            self::Other => 'Autre',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Enums;

enum StudyLevel: string
{
    case L1 = 'l1';
    case L2 = 'l2';
    case L3 = 'l3';
    case M1 = 'm1';
    case M2 = 'm2';

    public function label(): string
    {
        return strtoupper($this->value);
    }
}

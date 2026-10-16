<?php

declare(strict_types=1);

namespace App\Services\Offres;

use RuntimeException;

/**
 * Échec de lecture d'une page d'offre, avec un message montrable à l'utilisateur. `refuse` : le site
 * ne doit pas être lu (on propose alors de coller le texte de l'offre).
 */
class ErreurLecture extends RuntimeException
{
    public function __construct(string $message, public readonly bool $refuse = false)
    {
        parent::__construct($message);
    }
}

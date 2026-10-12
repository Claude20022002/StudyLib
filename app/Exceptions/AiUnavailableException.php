<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * L'assistant IA n'a pas répondu (clé absente, délai dépassé, erreur de l'API). L'appelant
 * propose un repli ou un message clair ; jamais une erreur 500.
 */
class AiUnavailableException extends RuntimeException {}

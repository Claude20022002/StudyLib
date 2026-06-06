<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HestimEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! str_ends_with(strtolower($value), '@hestim.ma')) {
            $fail('L\'adresse e-mail doit appartenir au domaine @hestim.ma.');
        }
    }
}

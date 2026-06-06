<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHestimEmail
{
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $email = $request->input('email');

        if (is_string($email) && ! $this->auth->emailBelongsToHestim($email)) {
            abort(422, 'Seules les adresses @hestim.ma sont autorisées.');
        }

        return $next($request);
    }
}

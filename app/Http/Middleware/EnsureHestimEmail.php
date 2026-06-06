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
            $message = 'Seules les adresses @hestim.ma sont autorisées.';

            if ($request->expectsJson()) {
                abort(422, $message);
            }

            return redirect()
                ->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => $message]);
        }

        return $next($request);
    }
}

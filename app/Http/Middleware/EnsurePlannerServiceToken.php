<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Auth\PlannerTokenVerifier;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Routes internes appelées par le serveur de Planner (espace de cours) : jeton de service en
 * Bearer, pour une opération (`scope`) et un document (`doc`). Les revendications sont posées
 * sur la requête (`planner_service`) ; le contrôleur compare `doc` au document demandé.
 */
class EnsurePlannerServiceToken
{
    public function __construct(private readonly PlannerTokenVerifier $verifier) {}

    public function handle(Request $request, Closure $next, string $scope): Response
    {
        $token = $request->bearerToken();
        if (! is_string($token) || $token === '') {
            abort(401);
        }
        try {
            $claims = $this->verifier->verifyService($token, $scope);
        } catch (Throwable $e) {
            Log::info('Jeton de service Planner refusé : '.$e->getMessage());
            abort(401);
        }
        $request->attributes->set('planner_service', $claims);

        return $next($request);
    }
}

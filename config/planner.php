<?php

declare(strict_types=1);

/*
 * Connexion unique avec HESTIM Planner (fournisseur d'identité de la plateforme).
 *
 * Planner signe les jetons d'accès en RS256 ; StudyLib les vérifie avec la clé publique publiée
 * en JWKS, sans secret partagé. Les comptes sont créés par l'administration dans Planner :
 * StudyLib n'a plus d'inscription ni de mot de passe local.
 */
return [
    // Adresse interne de l'API Planner (réseau Docker), sans barre finale
    'api_url' => rtrim((string) env('PLANNER_API_URL', 'http://backend:5000/api'), '/'),

    // Clés de vérification ; mises en cache, relues si un jeton porte un kid inconnu (rotation)
    'jwks_url' => env('PLANNER_JWKS_URL', rtrim((string) env('PLANNER_API_URL', 'http://backend:5000/api'), '/').'/.well-known/jwks.json'),
    'jwks_cache_seconds' => (int) env('PLANNER_JWKS_CACHE_SECONDS', 600),

    'issuer' => env('PLANNER_ISSUER', 'hestim-planner'),
    'audience' => 'studylib',
    // Tolérance d'horloge entre les deux serveurs (secondes)
    'leeway' => 30,

    // Cookie d'accès posé par Planner sur la même origine (préfixe __Host- en production)
    'cookies' => ['__Host-access_token', 'access_token'],

    // Page de connexion de Planner (même origine) ; `next` y ramène après connexion
    'login_url' => env('PLANNER_LOGIN_URL', '/connexion'),

    // Synchronisation des filières et modules (php artisan planner:sync)
    'integration_token' => env('PLANNER_INTEGRATION_TOKEN'),
];

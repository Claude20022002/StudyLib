<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;

/**
 * Simule HESTIM Planner dans les tests : une paire de clés RSA, son JWKS servi par Http::fake
 * et des jetons d'accès signés comme Planner les signe (RS256, kid, émetteur, audiences).
 */
trait IssuesPlannerTokens
{
    protected string $plannerPrivateKey;

    protected string $plannerKid = 'cle-de-test';

    protected function fakePlanner(array $extraResponses = []): void
    {
        [$this->plannerPrivateKey, $jwk] = $this->newRsaKey($this->plannerKid);
        config(['planner.jwks_url' => 'http://planner.test/api/.well-known/jwks.json', 'planner.api_url' => 'http://planner.test/api']);
        Http::fake($extraResponses + [
            'planner.test/api/.well-known/jwks.json' => Http::response(['keys' => [$jwk]]),
            'planner.test/api/auth/logout' => Http::response(['message' => 'Déconnexion réussie']),
        ]);
    }

    /**
     * @return array{0: string, 1: array<string, string>} clé privée PEM et JWK publique
     */
    protected function newRsaKey(string $kid): array
    {
        $options = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
        // Sous Windows, PHP n'a pas de configuration OpenSSL par défaut : celle livrée avec PHP
        $cnf = getenv('OPENSSL_CONF') ?: dirname(PHP_BINARY).'/extras/ssl/openssl.cnf';
        if (is_file($cnf)) {
            $options['config'] = $cnf;
        }
        $key = openssl_pkey_new($options);
        openssl_pkey_export($key, $pem, null, $options);
        $rsa = openssl_pkey_get_details($key)['rsa'];

        return [$pem, ['kty' => 'RSA', 'alg' => 'RS256', 'use' => 'sig', 'kid' => $kid, 'n' => JWT::urlsafeB64Encode($rsa['n']), 'e' => JWT::urlsafeB64Encode($rsa['e'])]];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function plannerToken(array $overrides = [], ?string $privateKey = null, ?string $kid = null): string
    {
        $payload = array_merge([
            'iss' => 'hestim-planner',
            'aud' => ['planner', 'studylib'],
            'sub' => '42',
            'sid' => 'session-1',
            'fid' => 'famille-1',
            'role' => 'etudiant',
            'email' => 'amina.etudiante@hestim.ma',
            'prenom' => 'Amina',
            'nom' => 'Etudiante',
            'filiere' => null,
            'annee' => 4,
            'iat' => time(),
            'exp' => time() + 900,
        ], $overrides);

        return JWT::encode($payload, $privateKey ?? $this->plannerPrivateKey, 'RS256', $kid ?? $this->plannerKid);
    }
}

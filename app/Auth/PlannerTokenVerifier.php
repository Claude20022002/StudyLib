<?php

declare(strict_types=1);

namespace App\Auth;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Http;
use UnexpectedValueException;

/**
 * Vérifie un jeton d'accès émis par HESTIM Planner.
 *
 * - Signature RS256 uniquement, avec une clé du JWKS de Planner choisie par son `kid`
 *   (php-jwt refuse « none », et un algorithme différent de celui de la clé, dont HS256).
 * - Expiration et date de début contrôlées, avec une petite tolérance d'horloge.
 * - Émetteur et audience « studylib » obligatoires : un jeton destiné à un autre service est refusé.
 * - Le JWKS est mis en cache ; un kid inconnu provoque une seule relecture (rotation des clés),
 *   au plus toutes les 30 secondes, pour qu'un jeton forgé ne fasse pas marteler Planner.
 */
final class PlannerTokenVerifier
{
    private const CACHE_KEY = 'planner:jwks';

    private const REFRESH_LOCK = 'planner:jwks:refresh';

    public function __construct(private readonly Cache $cache) {}

    /**
     * @return array<string, mixed> revendications du jeton
     *
     * @throws UnexpectedValueException si le jeton est invalide
     */
    public function verify(string $jwt): array
    {
        $kid = $this->kid($jwt);
        $jwks = $this->jwks();
        if (! $this->hasKey($jwks, $kid) && $this->cache->add(self::REFRESH_LOCK, true, 30)) {
            $this->cache->forget(self::CACHE_KEY);
            $jwks = $this->jwks();
        }
        if (! $this->hasKey($jwks, $kid)) {
            throw new UnexpectedValueException('Clé de signature inconnue');
        }

        JWT::$leeway = (int) config('planner.leeway');
        $claims = (array) JWT::decode($jwt, JWK::parseKeySet($jwks, 'RS256'));

        if (($claims['iss'] ?? null) !== config('planner.issuer')) {
            throw new UnexpectedValueException('Émetteur inattendu');
        }
        $audiences = (array) ($claims['aud'] ?? []);
        if (! in_array(config('planner.audience'), $audiences, true)) {
            throw new UnexpectedValueException('Jeton non destiné à StudyLib');
        }
        foreach (['sub', 'sid', 'email', 'role'] as $required) {
            if (! is_string($claims[$required] ?? null) || $claims[$required] === '') {
                throw new UnexpectedValueException("Revendication absente : {$required}");
            }
        }

        return $claims;
    }

    private function kid(string $jwt): ?string
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new UnexpectedValueException('Jeton mal formé');
        }
        $header = json_decode(JWT::urlsafeB64Decode($parts[0]), true);

        return is_array($header) && is_string($header['kid'] ?? null) ? $header['kid'] : null;
    }

    /**
     * @return array{keys: list<array<string, mixed>>}
     */
    private function jwks(): array
    {
        return $this->cache->remember(self::CACHE_KEY, (int) config('planner.jwks_cache_seconds'), function (): array {
            $body = Http::timeout(5)->acceptJson()->get((string) config('planner.jwks_url'))->throw()->json();
            // Seules des clés RSA de signature sont retenues
            $keys = array_values(array_filter(
                (array) ($body['keys'] ?? []),
                fn ($key) => is_array($key) && ($key['kty'] ?? null) === 'RSA' && ($key['use'] ?? 'sig') === 'sig' && isset($key['kid']),
            ));

            return ['keys' => $keys];
        });
    }

    /**
     * @param  array{keys: list<array<string, mixed>>}  $jwks
     */
    private function hasKey(array $jwks, ?string $kid): bool
    {
        return $kid !== null && in_array($kid, array_column($jwks['keys'], 'kid'), true);
    }
}

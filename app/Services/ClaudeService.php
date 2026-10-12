<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AiKind;
use App\Exceptions\AiUnavailableException;
use App\Models\AiRecommendation;
use App\Models\User;
use App\Repositories\Contracts\AiRecommendationRepositoryInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    public function __construct(
        private readonly AiRecommendationRepositoryInterface $recommendations,
    ) {}

    /**
     * Interroge l'API Claude pour générer des suggestions, puis trace la réponse.
     *
     * Délai borné : un appel lent ne retient pas un processus PHP. Toute panne (clé absente,
     * délai, erreur de l'API) devient une AiUnavailableException, que l'appelant traite.
     *
     * @return array<string, mixed>
     *
     * @throws AiUnavailableException
     */
    public function suggest(User $user, AiKind $kind, string $prompt, ?string $moduleId = null): array
    {
        $key = (string) config('services.claude.key');
        if ($key === '') {
            throw new AiUnavailableException("Clé de l'API Claude absente");
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $key,
                'anthropic-version' => '2023-06-01',
            ])
                ->connectTimeout(5)
                ->timeout((int) config('services.claude.timeout'))
                ->post((string) config('services.claude.url'), [
                    'model' => config('services.claude.model'),
                    'max_tokens' => 1024,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ])->throw()->json();
        } catch (ConnectionException|RequestException $e) {
            // Ni la demande ni la clé dans le journal : seulement de quoi comprendre la panne
            Log::warning('API Claude indisponible', [
                'kind' => $kind->value,
                'status' => $e instanceof RequestException ? $e->response->status() : null,
                'erreur' => $e instanceof ConnectionException ? 'connexion ou délai dépassé' : 'réponse en erreur',
            ]);

            throw new AiUnavailableException('API Claude indisponible', previous: $e);
        }

        if (! is_array($response)) {
            throw new AiUnavailableException("Réponse de l'API Claude illisible");
        }

        $this->persist($user, $kind, $prompt, $response, $moduleId);

        return $response;
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function persist(User $user, AiKind $kind, string $prompt, array $response, ?string $moduleId): AiRecommendation
    {
        return $this->recommendations->create([
            'user_id' => $user->getKey(),
            'kind' => $kind->value,
            'module_id' => $moduleId,
            'prompt' => $prompt,
            'response' => $response,
            'model' => $response['model'] ?? config('services.claude.model'),
            'tokens_used' => $response['usage']['output_tokens'] ?? null,
        ]);
    }
}

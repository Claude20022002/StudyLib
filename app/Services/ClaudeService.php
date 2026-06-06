<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AiKind;
use App\Models\AiRecommendation;
use App\Models\User;
use App\Repositories\Contracts\AiRecommendationRepositoryInterface;
use Illuminate\Support\Facades\Http;

class ClaudeService
{
    public function __construct(
        private readonly AiRecommendationRepositoryInterface $recommendations,
    ) {
    }

    /**
     * Interroge l'API Claude pour générer des suggestions, puis trace la réponse.
     *
     * @return array<string, mixed>
     */
    public function suggest(User $user, AiKind $kind, string $prompt, ?string $moduleId = null): array
    {
        $response = Http::withHeaders([
            'x-api-key' => (string) config('services.claude.key'),
            'anthropic-version' => '2023-06-01',
        ])->post((string) config('services.claude.url'), [
            'model' => config('services.claude.model'),
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ])->throw()->json();

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

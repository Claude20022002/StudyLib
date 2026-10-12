<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AiKind;
use App\Exceptions\AiUnavailableException;
use App\Http\Requests\Ai\StoreAiSuggestionRequest;
use App\Models\ProjectIdea;
use App\Services\ClaudeService;
use Illuminate\Http\JsonResponse;

class AiRecommendationController extends Controller
{
    public function __construct(
        private readonly ClaudeService $claude,
    ) {}

    public function store(StoreAiSuggestionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $kind = AiKind::from($validated['kind']);

        // Même règle que sur la page Projets : les idées générées servent le CV d'un étudiant
        if ($kind === AiKind::Project) {
            $this->authorize('generate', ProjectIdea::class);
        }

        try {
            $result = $this->claude->suggest(
                $request->user(),
                $kind,
                $validated['prompt'],
                $validated['module_id'] ?? null,
            );
        } catch (AiUnavailableException) {
            return response()->json([
                'message' => "L'assistant est momentanément indisponible. Réessayez dans un instant.",
            ], 503);
        }

        return response()->json($result);
    }
}

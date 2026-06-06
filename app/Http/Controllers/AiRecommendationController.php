<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AiKind;
use App\Http\Requests\Ai\StoreAiSuggestionRequest;
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

        $result = $this->claude->suggest(
            $request->user(),
            AiKind::from($validated['kind']),
            $validated['prompt'],
            $validated['module_id'] ?? null,
        );

        return response()->json($result);
    }
}

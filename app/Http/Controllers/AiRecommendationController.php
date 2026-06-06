<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AiKind;
use App\Services\ClaudeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiRecommendationController extends Controller
{
    public function __construct(
        private readonly ClaudeService $claude,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kind' => ['required', 'string', 'in:project,document,study_path,other'],
            'prompt' => ['required', 'string', 'max:2000'],
            'module_id' => ['nullable', 'uuid'],
        ]);

        $result = $this->claude->suggest(
            $request->user(),
            AiKind::from($validated['kind']),
            $validated['prompt'],
            $validated['module_id'] ?? null,
        );

        return response()->json($result);
    }
}

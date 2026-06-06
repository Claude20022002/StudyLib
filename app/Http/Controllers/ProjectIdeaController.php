<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ProjectIdea;
use App\Services\ProjectIdeaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectIdeaController extends Controller
{
    public function __construct(
        private readonly ProjectIdeaService $ideas,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['filiere_id', 'level', 'source']);

        return response()->json($this->ideas->search($filters));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', ProjectIdea::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'level' => ['required', 'string', 'in:l1,l2,l3,m1,m2'],
            'filiere_id' => ['nullable', 'uuid'],
            'repo_url' => ['nullable', 'url', 'max:500'],
        ]);

        return response()->json(
            $this->ideas->create($request->user(), $validated),
            201,
        );
    }
}

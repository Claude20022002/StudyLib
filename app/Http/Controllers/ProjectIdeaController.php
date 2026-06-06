<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectIdeaRequest;
use App\Models\ProjectIdea;
use App\Services\ProjectIdeaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectIdeaController extends Controller
{
    public function __construct(
        private readonly ProjectIdeaService $ideas,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['filiere_id', 'level', 'source']);

        return response()->json($this->ideas->search($filters));
    }

    public function store(StoreProjectIdeaRequest $request): JsonResponse
    {
        $this->authorize('create', ProjectIdea::class);

        return response()->json(
            $this->ideas->create($request->user(), $request->validated()),
            201,
        );
    }
}

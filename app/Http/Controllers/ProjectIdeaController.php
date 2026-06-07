<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectIdeaRequest;
use App\Models\ProjectIdea;
use App\Services\ProjectIdeaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectIdeaController extends Controller
{
    public function __construct(
        private readonly ProjectIdeaService $ideas,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->expectsJson()) {
            $filters = $request->only(['filiere_id', 'level', 'source', 'q', 'sort']);

            return response()->json($this->ideas->search($filters));
        }

        return view('pages.project-ideas.index');
    }

    public function store(StoreProjectIdeaRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', ProjectIdea::class);

        $idea = $this->ideas->create($request->user(), $request->validated());

        if ($request->expectsJson()) {
            return response()->json($idea->load(['filiere', 'user']), 201);
        }

        return redirect()
            ->route('project-ideas.index')
            ->with('success', 'Votre idée de projet a été publiée.');
    }
}

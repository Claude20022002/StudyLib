<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\InternshipReview;
use App\Services\InternshipReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternshipReviewController extends Controller
{
    public function __construct(
        private readonly InternshipReviewService $reviews,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['company_id', 'filiere_id', 'year_done', 'min_rating']);

        return response()->json($this->reviews->search($filters));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', InternshipReview::class);

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:150'],
            'company_city' => ['nullable', 'string', 'max:100'],
            'company_sector' => ['nullable', 'string', 'max:100'],
            'filiere_id' => ['nullable', 'uuid'],
            'position' => ['nullable', 'string', 'max:150'],
            'description' => ['required', 'string'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'year_level' => ['nullable', 'integer'],
            'year_done' => ['nullable', 'integer'],
            'is_paid' => ['boolean'],
        ]);

        return response()->json(
            $this->reviews->create($request->user(), $validated),
            201,
        );
    }
}

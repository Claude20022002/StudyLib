<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Internship\StoreInternshipReviewRequest;
use App\Models\InternshipReview;
use App\Services\InternshipReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternshipReviewController extends Controller
{
    public function __construct(
        private readonly InternshipReviewService $reviews,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['company_id', 'filiere_id', 'year_done', 'min_rating']);

        return response()->json($this->reviews->search($filters));
    }

    public function store(StoreInternshipReviewRequest $request): JsonResponse
    {
        $this->authorize('create', InternshipReview::class);

        return response()->json(
            $this->reviews->create($request->user(), $request->validated()),
            201,
        );
    }
}

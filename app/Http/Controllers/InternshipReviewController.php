<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Internship\StoreInternshipReviewRequest;
use App\Models\InternshipReview;
use App\Services\InternshipReviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InternshipReviewController extends Controller
{
    public function __construct(
        private readonly InternshipReviewService $reviews,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->expectsJson()) {
            $filters = $request->only([
                'company_id',
                'filiere_id',
                'year_done',
                'min_rating',
                'q',
                'city',
                'sector',
                'year_level',
                'sort',
            ]);

            return response()->json($this->reviews->browse($filters));
        }

        return view('pages.internship-reviews.index');
    }

    public function store(StoreInternshipReviewRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', InternshipReview::class);

        $review = $this->reviews->create($request->user(), $request->validated());

        if ($request->expectsJson()) {
            return response()->json($review->load(['company', 'filiere']), 201);
        }

        return redirect()
            ->route('internship-reviews.index')
            ->with('success', 'Votre retour de stage a été publié. Merci pour le partage !');
    }
}

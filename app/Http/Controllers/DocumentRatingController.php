<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\RatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentRatingController extends Controller
{
    public function __construct(
        private readonly RatingService $ratings,
    ) {
    }

    public function store(Request $request, Document $document): JsonResponse
    {
        $validated = $request->validate([
            'score' => ['required', 'integer', 'between:1,5'],
        ]);

        $rating = $this->ratings->rate($request->user(), $document, $validated['score']);

        return response()->json($rating, 201);
    }
}

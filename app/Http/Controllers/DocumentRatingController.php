<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Document\StoreDocumentRatingRequest;
use App\Models\Document;
use App\Services\RatingService;
use Illuminate\Http\JsonResponse;

class DocumentRatingController extends Controller
{
    public function __construct(
        private readonly RatingService $ratings,
    ) {
    }

    public function store(StoreDocumentRatingRequest $request, Document $document): JsonResponse
    {
        $rating = $this->ratings->rate(
            $request->user(),
            $document,
            $request->validated('score'),
        );

        return response()->json($rating, 201);
    }
}

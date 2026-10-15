<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InternshipReviewMobileService;
use App\Services\InternshipReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Écran « Stages » de l'application : tous les retours partagés (filtrables), les miens, et les
 * valeurs possibles des filtres (villes, secteurs, années).
 */
class InternshipReviewMobileController extends Controller
{
    public function __construct(
        private readonly InternshipReviewMobileService $mobile,
        private readonly InternshipReviewService $reviews,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filtres = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:120'],
            'sector' => ['nullable', 'string', 'max:120'],
            'year_done' => ['nullable', 'integer', 'between:2000,2100'],
            'is_paid' => ['nullable', 'in:0,1,true,false'],
            'filiere' => ['nullable', 'string', 'max:20'],
        ]);

        return response()->json($this->mobile->lister($filtres));
    }

    public function mine(Request $request): JsonResponse
    {
        return response()->json($this->mobile->miens($request->user()));
    }

    public function filters(): JsonResponse
    {
        return response()->json($this->reviews->filterOptions());
    }
}

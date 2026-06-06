<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\FiliereService;
use Illuminate\Http\JsonResponse;

class FiliereController extends Controller
{
    public function __construct(
        private readonly FiliereService $filieres,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->filieres->all());
    }
}

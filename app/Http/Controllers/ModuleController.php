<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Filiere;
use App\Services\ModuleService;
use Illuminate\Http\JsonResponse;

class ModuleController extends Controller
{
    public function __construct(
        private readonly ModuleService $modules,
    ) {}

    public function index(Filiere $filiere): JsonResponse
    {
        return response()->json(
            $this->modules->forFiliere($filiere),
        );
    }
}

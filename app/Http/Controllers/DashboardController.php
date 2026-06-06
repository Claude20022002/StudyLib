<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\EventService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private readonly EventService $events,
    ) {}

    public function index(): JsonResponse
    {
        // Les vues Blade seront branchées ultérieurement.
        return response()->json([
            'upcoming_events' => $this->events->upcoming(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\EventService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService $events,
    ) {}

    public function index(Request $request): View|JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json($this->events->upcoming());
        }

        // Les événements étudiants ne font pas partie de l'espace de l'enseignant
        if ($request->user()?->isTeacher()) {
            return redirect()->route('dashboard');
        }

        return view('pages.events.index');
    }
}

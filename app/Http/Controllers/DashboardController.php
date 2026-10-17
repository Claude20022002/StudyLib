<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** L'enseignant a son tableau de bord dans Planner (lot E-9) ; les autres restent ici. */
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()?->isTeacher()) {
            return redirect()->away(config('planner.pages.tableau'));
        }

        return view('pages.dashboard.index');
    }
}

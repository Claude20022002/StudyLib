<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use App\Services\FiliereService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    public function create(FiliereService $filieres): View
    {
        return view('pages.auth.register', [
            'filieres' => $filieres->all(),
        ]);
    }

    public function store(RegisterRequest $request): RedirectResponse|JsonResponse
    {
        $user = $this->auth->register($request->validated());

        Auth::login($user);

        if ($request->expectsJson()) {
            return response()->json($user->load('filiere'), 201);
        }

        return redirect()->route('dashboard');
    }
}

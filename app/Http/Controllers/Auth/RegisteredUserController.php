<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    public function store(RegisterRequest $request): JsonResponse
    {
        $user = $this->auth->register($request->validated());

        Auth::login($user);

        return response()->json($user->load('filiere'), 201);
    }
}

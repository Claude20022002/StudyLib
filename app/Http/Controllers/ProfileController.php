<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profile,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->load('filiere'),
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $this->authorize('update', $request->user());

        $user = $this->profile->update(
            $request->user(),
            $request->validated(),
            $request->file('avatar'),
        );

        return response()->json($user->load('filiere'));
    }
}

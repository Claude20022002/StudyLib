<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profile,
    ) {}

    public function show(Request $request): View|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(
                $request->user()->load('filiere'),
            );
        }

        return view('pages.profile.show', [
            'header' => 'Mon profil',
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $request->user());

        $user = $this->profile->update(
            $request->user(),
            $request->validated(),
            $request->file('avatar'),
        );

        if ($request->expectsJson()) {
            return response()->json($user->load('filiere'));
        }

        return back()->with('success', 'Profil mis à jour.');
    }
}

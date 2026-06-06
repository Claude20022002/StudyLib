<?php

declare(strict_types=1);

namespace App\Livewire\Layout;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class AdminSidebar extends Component
{
    public function render(): View
    {
        $user = Auth::user();

        return view('livewire.layout.admin-sidebar', [
            'items' => config('studylib.nav_admin', []),
            'user' => $user,
            'initials' => $this->initials($user?->name),
        ]);
    }

    public function isActive(?string $routeName): bool
    {
        return $routeName !== null && Route::has($routeName) && request()->routeIs($routeName);
    }

    private function initials(?string $name): string
    {
        if ($name === null || $name === '') {
            return '?';
        }

        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return strtoupper(collect($parts)->take(2)->map(fn (string $part) => mb_substr($part, 0, 1))->implode(''));
    }
}

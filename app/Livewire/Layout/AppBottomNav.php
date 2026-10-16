<?php

declare(strict_types=1);

namespace App\Livewire\Layout;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class AppBottomNav extends Component
{
    public function render(): View
    {
        return view('livewire.layout.app-bottom-nav', [
            // 'sauf' : rôles qui ne voient pas l'entrée, comme dans le menu (AppSidebar)
            'items' => array_values(array_filter(
                config('studylib.nav.mobile', []),
                fn (array $item): bool => ! in_array(Auth::user()?->role?->value, $item['sauf'] ?? [], true),
            )),
        ]);
    }

    public function isActive(?string $routeName): bool
    {
        return $routeName !== null && Route::has($routeName) && request()->routeIs($routeName);
    }

    public function hrefFor(array $item): ?string
    {
        if (empty($item['route']) || ! Route::has($item['route'])) {
            return null;
        }

        return route($item['route']);
    }

    public function showDepositFab(): bool
    {
        return ! request()->routeIs('documents.*');
    }
}

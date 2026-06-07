<?php

declare(strict_types=1);

namespace App\Livewire\Layout;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class AppBottomNav extends Component
{
    public function render(): View
    {
        return view('livewire.layout.app-bottom-nav', [
            'items' => config('studylib.nav.mobile', []),
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

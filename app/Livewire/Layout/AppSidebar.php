<?php

declare(strict_types=1);

namespace App\Livewire\Layout;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class AppSidebar extends Component
{
    /** @var array<string, int|string|null> */
    public array $counts = [];

    public function render(): View
    {
        return view('livewire.layout.app-sidebar', [
            'mainNav' => config('studylib.nav.main', []),
            'personalNav' => config('studylib.nav.personal', []),
        ]);
    }

    public function isActive(?string $routeName, array $query = []): bool
    {
        if ($routeName === null || ! Route::has($routeName)) {
            return false;
        }

        if (! request()->routeIs($routeName)) {
            return false;
        }

        foreach ($query as $key => $value) {
            if ((string) request()->query($key) !== (string) $value) {
                return false;
            }
        }

        return true;
    }

    public function hrefFor(array $item): ?string
    {
        if (! empty($item['disabled']) || empty($item['route']) || ! Route::has($item['route'])) {
            return null;
        }

        $parameters = $item['query'] ?? [];

        return route($item['route'], $parameters);
    }
}

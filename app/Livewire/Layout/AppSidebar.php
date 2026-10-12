<?php

declare(strict_types=1);

namespace App\Livewire\Layout;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class AppSidebar extends Component
{
    /** @var array<string, int|string|null> */
    public array $counts = [];

    public function render(): View
    {
        return view('livewire.layout.app-sidebar', [
            'mainNav' => $this->pourLeRole(config('studylib.nav.main', [])),
            'personalNav' => $this->pourLeRole(config('studylib.nav.personal', [])),
        ]);
    }

    /**
     * Entrées du menu visibles pour le rôle connecté ('sauf' => rôles exclus).
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function pourLeRole(array $items): array
    {
        $role = Auth::user()?->role?->value;

        return array_values(array_filter(
            $items,
            fn (array $item): bool => ! in_array($role, $item['sauf'] ?? [], true),
        ));
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

    public function showDepositPromo(): bool
    {
        return ! request()->routeIs('dashboard', 'documents.*', 'profile.show');
    }
}

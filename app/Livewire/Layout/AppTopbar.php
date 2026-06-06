<?php

declare(strict_types=1);

namespace App\Livewire\Layout;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AppTopbar extends Component
{
    public ?string $title = null;

    public function render(): View
    {
        $user = Auth::user();

        return view('livewire.layout.app-topbar', [
            'user' => $user,
            'initials' => $this->initials($user?->name),
        ]);
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

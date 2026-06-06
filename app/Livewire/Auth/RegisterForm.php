<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class RegisterForm extends Component
{
    /** @var Collection<int, \App\Models\Filiere> */
    public Collection $filieres;

    public function mount(Collection $filieres): void
    {
        $this->filieres = $filieres;
    }

    public function render(): View
    {
        return view('livewire.auth.register-form');
    }
}

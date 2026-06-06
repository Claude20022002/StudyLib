<?php

declare(strict_types=1);

namespace App\Livewire\Ui;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class SearchBar extends Component
{
    #[Modelable]
    public string $query = '';

    public string $placeholder = 'Rechercher un cours, un module, un auteur…';

    public bool $showShortcut = true;

    public function updatedQuery(string $value): void
    {
        $this->dispatch('search-updated', query: $value);
    }

    public function render(): View
    {
        return view('livewire.ui.search-bar');
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Ui;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class FilterChips extends Component
{
    public string $active = 'all';

    /** @var array<int, array{value: string, label: string}> */
    public array $filters = [];

    public function mount(): void
    {
        if ($this->filters === []) {
            $this->filters = config('studylib.document_filters', []);
        }
    }

    public function select(string $value): void
    {
        $this->active = $value;
        $this->dispatch('filter-changed', filter: $value);
    }

    public function render(): View
    {
        return view('livewire.ui.filter-chips');
    }
}

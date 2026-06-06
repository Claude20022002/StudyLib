<div {{ $attributes->merge(['class' => 'flex gap-1.5']) }} role="group" aria-label="Filtrer par type de document">
    @foreach ($filters as $filter)
        <button
            type="button"
            wire:click="select('{{ $filter['value'] }}')"
            @class(['sl-chip', 'is-active' => $active === $filter['value']])
            aria-pressed="{{ $active === $filter['value'] ? 'true' : 'false' }}"
        >
            {{ $filter['label'] }}
        </button>
    @endforeach
</div>

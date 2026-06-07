@props([
    'idea',
])

<article {{ $attributes->merge(['class' => 'sl-prof-item']) }}>
    <div class="sl-prof-item__ico sl-prof-item__ico--project" aria-hidden="true">
        <x-ui.icon name="layers" class="h-5 w-5" />
    </div>
    <div class="min-w-0 flex-1">
        <div class="mb-1 flex flex-wrap gap-1.5">
            <x-ui.badge variant="primary">{{ $idea->level?->label() ?? 'Projet' }}</x-ui.badge>
            <x-ui.badge variant="neutral">{{ $idea->source?->label() ?? 'Étudiant' }}</x-ui.badge>
        </div>
        <h3 class="text-h4 leading-snug font-semibold">{{ $idea->title }}</h3>
        <div class="mt-1.5 flex flex-wrap items-center gap-4 text-sm text-muted">
            @if ($idea->repo_url)
                <span>GitHub lié</span>
            @endif
            <span>{{ $idea->created_at?->diffForHumans() }}</span>
        </div>
    </div>
    <div class="flex shrink-0 gap-2">
        <x-ui.button variant="ghost" size="sm" href="{{ route('project-ideas.index') }}" wire:navigate>
            Voir
        </x-ui.button>
    </div>
</article>

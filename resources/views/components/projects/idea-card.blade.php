@props([
    'idea',
    'ideaService',
])

@php
    $level = $idea->level;
    $difficulty = $level instanceof \App\Enums\StudyLevel
        ? $ideaService->difficultyDots($level)
        : 2;
@endphp

<article
    {{ $attributes->merge(['class' => 'sl-prj-card']) }}
    wire:click="openDetail('{{ $idea->id }}')"
    role="button"
    tabindex="0"
    wire:key="idea-{{ $idea->id }}"
>
    <div class="sl-prj-card__top">
        @if ($idea->source === \App\Enums\IdeaSource::Ai)
            <span class="sl-prj-source sl-prj-source--ai">
                <x-ui.icon name="sparkles" class="h-3 w-3" />
                IA
            </span>
        @else
            <span class="sl-prj-source sl-prj-source--student">
                <x-ui.icon name="user" class="h-3 w-3" />
                Étudiant
            </span>
        @endif
        <h3 class="sl-prj-card__title">{{ $idea->title }}</h3>
    </div>

    <p class="sl-prj-card__desc">{{ $idea->description }}</p>

    <div class="sl-prj-card__badges">
        @if ($level)
            <x-ui.badge variant="primary">{{ $level->label() }}</x-ui.badge>
        @endif
        @if ($idea->filiere)
            <x-ui.badge variant="neutral">{{ $idea->filiere->name }}</x-ui.badge>
        @endif
    </div>

    <div class="sl-prj-card__meta">
        <span class="sl-prj-card__mi">
            <span class="sl-prj-diff" aria-label="Difficulté estimée">
                @for ($dot = 1; $dot <= 3; $dot++)
                    <span @class(['sl-prj-diff__dot', 'is-on' => $dot <= $difficulty])></span>
                @endfor
            </span>
        </span>
        @if ($idea->repo_url)
            <span class="sl-prj-card__mi">
                <x-ui.icon name="grid" class="h-3.5 w-3.5" />
                GitHub
            </span>
        @endif
        <span class="sl-prj-card__mi">{{ $idea->created_at?->diffForHumans() }}</span>
    </div>

    <div class="sl-prj-card__foot">
        <div class="sl-prj-doers">
            <span class="sl-prj-doers__av" aria-hidden="true">{{ $ideaService->authorInitials($idea) }}</span>
            <span class="sl-prj-doers__name">{{ $ideaService->maskedAuthorName($idea) }}</span>
        </div>
        <span class="text-sm font-semibold text-primary">Voir la fiche →</span>
    </div>
</article>

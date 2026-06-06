@props([
    'completion' => 0,
    'internshipCount' => '0',
])

<div class="sl-ai-panel">
    <span class="sl-ai-tag">
        <x-ui.icon name="sparkles" class="h-[13px] w-[13px]" />
        Suggestion IA
    </span>
    <h3 class="relative mt-3 mb-2 text-h3 font-bold tracking-tight">Projet CV à démarrer</h3>
    <p class="relative mb-4 text-sm opacity-90">
        Votre profil est complet à {{ $completion }} %.
        Lancez votre CV étudiant pour postuler aux {{ $internshipCount }} stages recommandés cette semaine.
    </p>
    <x-ui.button variant="primary" href="{{ route('project-ideas.index') }}" class="relative w-full !bg-white !text-primary hover:!bg-surface">
        Démarrer mon CV
        <x-ui.icon name="arrow-right" />
    </x-ui.button>
</div>

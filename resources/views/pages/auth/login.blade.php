<x-layouts.guest :title="'Connexion · '.config('app.name')">
    <div class="sl-guest-form-side">
        <div class="flex items-center gap-3">
            <x-ui.brand />
            <a href="{{ route('home') }}" class="ml-auto inline-flex items-center gap-1.5 text-sm text-muted transition-colors hover:text-primary">
                <x-ui.icon name="chevron-left" class="h-[15px] w-[15px]" />
                Retour
            </a>
        </div>

        <div class="grid flex-1 place-items-center py-8">
            <livewire:auth.login-form />
        </div>

        <p class="sl-auth-footer-note">Réservé aux étudiants HESTIM · @hestim.ma</p>
        <x-ui.flaticon-attribution />
    </div>

    @include('partials.auth.art-panel')
</x-layouts.guest>

<form
    method="POST"
    action="{{ route('login') }}"
    class="w-full max-w-[400px]"
    x-data="{ showPassword: false, loading: false }"
    @submit="loading = true"
    novalidate
>
    @csrf

    <div class="mb-8">
        <h1 class="text-h1 leading-tight font-bold tracking-tight">Bon retour 👋</h1>
        <p class="mt-2 text-ink-soft">Connectez-vous pour accéder à la bibliothèque de votre promo.</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 flex items-start gap-3 rounded-md bg-danger-soft p-4 text-sm text-danger-ink" role="alert">
            <div class="grid h-[34px] w-[34px] shrink-0 place-items-center rounded-[10px] bg-white text-danger">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
            </div>
            <div>
                <div class="text-body font-semibold">Connexion impossible</div>
                <div>{{ $errors->first() }}</div>
            </div>
        </div>
    @endif

    <x-ui.domain-note class="mb-6">
        Seules les adresses <strong class="font-semibold">@hestim.ma</strong> sont acceptées.
    </x-ui.domain-note>

    <x-ui.field label="Email institutionnel" :error="$errors->first('email')">
        <div class="relative">
            <span class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-muted" aria-hidden="true">
                <x-ui.icon name="mail" class="h-[18px] w-[18px]" />
            </span>
            <input
                id="email"
                name="email"
                type="email"
                inputmode="email"
                autocomplete="email"
                value="{{ old('email') }}"
                placeholder="prenom.nom@hestim.ma"
                required
                class="sl-input sl-input--lg w-full @error('email') is-error @enderror"
                aria-describedby="@error('email') email-error @else email-hint @enderror"
            />
        </div>
    </x-ui.field>

    <div class="sl-field mb-5">
        <div class="flex items-baseline justify-between">
            <label for="password" class="text-sm font-semibold text-ink-soft">Mot de passe</label>
            <a href="#" class="text-sm font-medium text-primary hover:text-primary-hover hover:underline">Mot de passe oublié ?</a>
        </div>
        <div class="relative">
            <span class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-muted" aria-hidden="true">
                <x-ui.icon name="lock" class="h-[18px] w-[18px]" />
            </span>
            <input
                id="password"
                name="password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                placeholder="Votre mot de passe"
                required
                class="sl-input sl-input--lg w-full pr-12 @error('password') is-error @enderror"
                style="padding-right: 48px"
            />
            <button
                type="button"
                class="absolute top-1/2 right-3 grid h-[30px] w-[30px] -translate-y-1/2 place-items-center rounded-[7px] border-0 bg-transparent text-muted hover:bg-surface-2 hover:text-ink"
                @click="showPassword = !showPassword"
                :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
            >
                <span x-show="!showPassword"><x-ui.icon name="eye" class="h-[18px] w-[18px]" /></span>
                <span x-show="showPassword" x-cloak><x-ui.icon name="eye-off" class="h-[18px] w-[18px]" /></span>
            </button>
        </div>
        @error('password')
            <p class="sl-field-error" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <label class="mb-6 flex items-center gap-2 text-sm text-ink-soft">
        <input type="checkbox" name="remember" value="1" class="rounded border-border-strong text-primary focus:ring-primary" @checked(old('remember')) />
        Se souvenir de moi
    </label>

    <button
        type="submit"
        class="sl-btn sl-btn--primary sl-btn--lg w-full shadow-sm"
        :class="{ 'opacity-80': loading }"
        :disabled="loading"
    >
        <span class="inline-block h-[18px] w-[18px] animate-spin rounded-full border-2 border-white/40 border-t-white" x-show="loading" x-cloak aria-hidden="true"></span>
        <span :class="{ 'opacity-85': loading }">Se connecter</span>
    </button>

    <p class="mt-6 text-center text-sm text-muted">
        Pas encore de compte ?
        <a href="#" class="font-semibold text-primary">Créer un compte</a>
    </p>
</form>

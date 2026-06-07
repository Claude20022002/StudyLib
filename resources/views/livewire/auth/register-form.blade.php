<form
    method="POST"
    action="{{ route('register.store') }}"
    class="w-full max-w-[400px]"
    x-data="{ showPassword: false, showPasswordConfirm: false, loading: false }"
    @submit="loading = true"
    novalidate
>
    @csrf

    <div class="mb-8">
        <h1 class="sl-auth-title font-bold">Activez votre accès HESTIM</h1>
        <p class="mt-2 text-ink-soft">Créez votre compte pour rejoindre la bibliothèque de votre promo.</p>
    </div>

    @if ($errors->any())
        <x-ui.page-alert variant="danger" title="Inscription impossible">
            {{ $errors->first() }}
        </x-ui.page-alert>
    @endif

    <x-ui.domain-note class="mb-6">
        Seules les adresses <strong class="font-semibold">@hestim.ma</strong> sont acceptées.
    </x-ui.domain-note>

    <x-ui.field label="Nom complet" id="name" class="mb-5" :error="$errors->first('name')">
        <div class="relative">
            <span class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-muted" aria-hidden="true">
                <x-ui.icon name="user" class="h-[18px] w-[18px]" />
            </span>
            <input
                id="name"
                name="name"
                type="text"
                autocomplete="name"
                value="{{ old('name') }}"
                placeholder="Prénom Nom"
                required
                class="sl-input sl-input--lg w-full @error('name') is-error @enderror"
                aria-describedby="@error('name') name-error @else name-hint @enderror"
            />
        </div>
    </x-ui.field>

    <x-ui.field label="Email institutionnel" id="email" class="mb-5" :error="$errors->first('email')">
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
        <label for="password" class="text-sm font-semibold text-ink-soft">Mot de passe</label>
        <div class="relative">
            <span class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-muted" aria-hidden="true">
                <x-ui.icon name="lock" class="h-[18px] w-[18px]" />
            </span>
            <input
                id="password"
                name="password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                placeholder="Minimum 8 caractères"
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

    <div class="sl-field mb-5">
        <label for="password_confirmation" class="text-sm font-semibold text-ink-soft">Confirmer le mot de passe</label>
        <div class="relative">
            <span class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-muted" aria-hidden="true">
                <x-ui.icon name="lock" class="h-[18px] w-[18px]" />
            </span>
            <input
                id="password_confirmation"
                name="password_confirmation"
                :type="showPasswordConfirm ? 'text' : 'password'"
                autocomplete="new-password"
                placeholder="Répétez le mot de passe"
                required
                class="sl-input sl-input--lg w-full pr-12"
                style="padding-right: 48px"
            />
            <button
                type="button"
                class="absolute top-1/2 right-3 grid h-[30px] w-[30px] -translate-y-1/2 place-items-center rounded-[7px] border-0 bg-transparent text-muted hover:bg-surface-2 hover:text-ink"
                @click="showPasswordConfirm = !showPasswordConfirm"
                :aria-label="showPasswordConfirm ? 'Masquer la confirmation' : 'Afficher la confirmation'"
            >
                <span x-show="!showPasswordConfirm"><x-ui.icon name="eye" class="h-[18px] w-[18px]" /></span>
                <span x-show="showPasswordConfirm" x-cloak><x-ui.icon name="eye-off" class="h-[18px] w-[18px]" /></span>
            </button>
        </div>
    </div>

    <x-ui.field label="Filière" id="filiere_id" class="mb-5" hint="Optionnel · vous pourrez la modifier plus tard." :error="$errors->first('filiere_id')">
        <select
            id="filiere_id"
            name="filiere_id"
            class="sl-input sl-input--lg w-full @error('filiere_id') is-error @enderror"
            aria-describedby="@error('filiere_id') filiere_id-error @else filiere_id-hint @enderror"
        >
            <option value="">Sélectionnez votre filière</option>
            @foreach ($filieres as $filiere)
                <option value="{{ $filiere->id }}" @selected(old('filiere_id') === $filiere->id)>
                    {{ $filiere->name }} ({{ $filiere->code }})
                </option>
            @endforeach
        </select>
    </x-ui.field>

    <x-ui.field label="Année d'études" id="year_level" class="mb-6" hint="Optionnel" :error="$errors->first('year_level')">
        <select
            id="year_level"
            name="year_level"
            class="sl-input sl-input--lg w-full @error('year_level') is-error @enderror"
            aria-describedby="@error('year_level') year_level-error @else year_level-hint @enderror"
        >
            <option value="">Sélectionnez votre année</option>
            @foreach ([1 => 'L1', 2 => 'L2', 3 => 'L3', 4 => 'M1', 5 => 'M2'] as $level => $label)
                <option value="{{ $level }}" @selected((int) old('year_level') === $level)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </x-ui.field>

    <button
        type="submit"
        class="sl-btn sl-btn--primary sl-btn--lg w-full shadow-sm"
        :class="{ 'opacity-80': loading }"
        :disabled="loading"
    >
        <span class="inline-block h-[18px] w-[18px] animate-spin rounded-full border-2 border-white/40 border-t-white" x-show="loading" x-cloak aria-hidden="true"></span>
        <span :class="{ 'opacity-85': loading }">Créer mon compte</span>
    </button>

    <p class="mt-6 text-center text-sm text-muted">
        Déjà inscrit ?
        <a href="{{ route('login') }}" class="font-semibold text-primary">Se connecter</a>
    </p>
</form>

<x-layouts.marketing :title="'StudyLib · Tous vos cours au même endroit'">
    <header
        class="sl-landing-header"
        x-data="{ menuOpen: false }"
        @keydown.escape.window="menuOpen = false"
    >
        <div class="sl-landing-wrap sl-landing-header__inner">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                <x-ui.brand />
            </a>

            <nav class="sl-landing-nav hidden md:flex" aria-label="Navigation principale">
                <a href="#features" class="sl-landing-nav-link">Fonctionnalités</a>
                <a href="#trust" class="sl-landing-nav-link">Sécurité</a>
                <a href="#stats" class="sl-landing-nav-link">Communauté</a>
            </nav>

            <div class="sl-landing-header__actions">
                @auth
                    <x-ui.button href="{{ route('dashboard') }}" variant="primary" size="sm">
                        Tableau de bord
                    </x-ui.button>
                @else
                    <x-ui.button href="{{ route('login') }}" variant="secondary" size="sm" class="hidden sm:inline-flex">
                        Se connecter
                    </x-ui.button>
                    <x-ui.button href="{{ route('register') }}" variant="primary" size="sm" class="hidden sm:inline-flex">
                        Créer un compte
                    </x-ui.button>
                @endauth

                <button
                    type="button"
                    class="sl-landing-menu-btn md:hidden"
                    @click="menuOpen = !menuOpen"
                    :aria-expanded="menuOpen.toString()"
                    aria-controls="landing-mobile-nav"
                    aria-label="Menu"
                >
                    <x-ui.icon name="grid" class="h-5 w-5" />
                </button>
            </div>
        </div>

        <nav
            id="landing-mobile-nav"
            class="sl-landing-mobile-nav md:hidden"
            x-show="menuOpen"
            x-cloak
            x-transition
            aria-label="Navigation mobile"
        >
            <div class="sl-landing-wrap flex flex-col gap-1 py-3">
                <a href="#features" class="sl-landing-mobile-link" @click="menuOpen = false">Fonctionnalités</a>
                <a href="#trust" class="sl-landing-mobile-link" @click="menuOpen = false">Sécurité</a>
                <a href="#stats" class="sl-landing-mobile-link" @click="menuOpen = false">Communauté</a>
                @guest
                    <a href="{{ route('login') }}" class="sl-landing-mobile-link" @click="menuOpen = false">Se connecter</a>
                    <a href="{{ route('register') }}" class="sl-landing-mobile-link" @click="menuOpen = false">Créer un compte</a>
                @endguest
            </div>
        </nav>
    </header>

    <section class="sl-landing-hero">
        <div class="sl-landing-wrap sl-landing-hero__grid">
            <div class="sl-landing-hero__copy">
                <span class="sl-landing-pill">
                    <span class="sl-landing-pill__dot" aria-hidden="true"></span>
                    Réservé à la communauté HESTIM
                </span>

                <h1 class="sl-landing-hero__title">
                    Tous vos cours, examens, stages et projets
                    <span class="sl-landing-hero__accent">au même endroit</span>
                </h1>

                <p class="sl-landing-hero__sub">
                    La plateforme collaborative de ressources pédagogiques réservée aux étudiants HESTIM. Partagez, trouvez et révisez, ensemble.
                </p>

                <div class="sl-landing-hero__cta">
                    @auth
                        <x-ui.button href="{{ route('dashboard') }}" variant="primary" size="lg" class="shadow-sm hover:-translate-y-px hover:shadow-md">
                            <x-ui.icon name="library" class="h-4 w-4" />
                            Accéder au tableau de bord
                        </x-ui.button>
                    @else
                        <x-ui.button href="{{ route('login') }}" variant="primary" size="lg" class="shadow-sm hover:-translate-y-px hover:shadow-md">
                            <x-ui.icon name="mail" class="h-4 w-4" />
                            Se connecter avec mon email @hestim.ma
                        </x-ui.button>
                    @endauth
                    <x-ui.button href="#features" variant="secondary" size="lg">
                        Découvrir les fonctionnalités
                    </x-ui.button>
                </div>

                <p class="sl-landing-hero__fine">
                    <x-ui.icon name="check" class="h-4 w-4 text-success" />
                    Accès gratuit · Vérification par email institutionnel
                </p>
            </div>

            <div class="sl-landing-hero__visual">
                <div class="sl-landing-float sl-landing-float--tl">
                    <div class="sl-landing-float__ico bg-success-soft text-success">
                        <x-ui.icon name="shield-check" class="h-4 w-4" />
                    </div>
                    <div>
                        <div class="text-sm font-semibold">Compte vérifié</div>
                        <div class="text-xs text-muted">@hestim.ma</div>
                    </div>
                </div>

                <div class="sl-landing-mock">
                    <div class="sl-landing-mock__bar">
                        <span class="sl-landing-mock__dot"></span>
                        <span class="sl-landing-mock__dot"></span>
                        <span class="sl-landing-mock__dot"></span>
                        <span class="sl-landing-mock__search"></span>
                    </div>
                    <div class="sl-landing-mock__body">
                        @foreach ([
                            ['tag' => 'PDF', 'from' => '#EEF2F7'],
                            ['tag' => 'DOCX', 'from' => '#EAF1FB'],
                            ['tag' => 'SLIDES', 'from' => '#F0EEFB'],
                            ['tag' => 'PDF', 'from' => '#EAF6EF'],
                        ] as $card)
                            <div class="sl-landing-mock__card">
                                <div
                                    class="sl-landing-mock__thumb"
                                    style="background-image: repeating-linear-gradient(135deg, {{ $card['from'] }} 0 10px, #E6EBF2 10px 20px)"
                                >
                                    <span class="sl-landing-mock__tag">{{ $card['tag'] }}</span>
                                </div>
                                <div class="sl-landing-mock__lines">
                                    <span class="sl-landing-mock__line"></span>
                                    <span class="sl-landing-mock__line sl-landing-mock__line--short"></span>
                                    <div class="mt-0.5 flex items-center gap-1.5">
                                        <span class="sl-landing-mock__ava"></span>
                                        <span class="ml-auto flex gap-0.5 text-star">
                                            @for ($i = 0; $i < 4; $i++)
                                                <svg class="h-[9px] w-[9px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 5.9 6.5.95-4.7 4.58 1.1 6.47L12 17.4l-5.8 3.05 1.1-6.47L2.6 9.35l6.5-.95z"/></svg>
                                            @endfor
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="sl-landing-float sl-landing-float--br">
                    <div class="sl-landing-float__ico bg-primary-soft text-primary">
                        <x-ui.icon name="sparkles" class="h-4 w-4" />
                    </div>
                    <div>
                        <div class="text-sm font-semibold">+1 240 ressources</div>
                        <div class="text-xs text-muted">partagées par la promo</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="sl-landing-stats" id="stats" aria-label="Chiffres clés de la communauté">
        <div class="sl-landing-wrap sl-landing-stats__grid">
            <div class="sl-landing-stat">
                <span class="sl-landing-stat__val">1 240+</span>
                <span class="sl-landing-stat__lbl">Ressources partagées</span>
            </div>
            <div class="sl-landing-stat">
                <span class="sl-landing-stat__val">850+</span>
                <span class="sl-landing-stat__lbl">Étudiants actifs</span>
            </div>
            <div class="sl-landing-stat">
                <span class="sl-landing-stat__val">4</span>
                <span class="sl-landing-stat__lbl">Filières couvertes</span>
            </div>
            <div class="sl-landing-stat">
                <span class="sl-landing-stat__val">100%</span>
                <span class="sl-landing-stat__lbl">Accès @hestim.ma</span>
            </div>
        </div>
    </section>

    <section class="sl-landing-sec" id="features">
        <div class="sl-landing-wrap">
            <div class="sl-landing-sec-head">
                <span class="sl-landing-eyebrow">Pourquoi StudyLib</span>
                <h2>Une seule plateforme pour toute votre scolarité</h2>
                <p>Pensée par et pour les étudiants HESTIM, du premier cours au rapport de stage.</p>
            </div>

            <div class="sl-landing-values">
                <article class="sl-landing-value sl-landing-value--library">
                    <div class="sl-landing-value__ico sl-landing-value__ico--flaticon">
                        <x-ui.flaticon name="library" class="sl-flaticon--landing" />
                    </div>
                    <h3>Ressources centralisées</h3>
                    <p>Cours, annales, TD corrigés, rapports de stage et projets : tout est rangé par filière et par semestre, accessible en quelques secondes.</p>
                </article>
                <article class="sl-landing-value sl-landing-value--community">
                    <div class="sl-landing-value__ico sl-landing-value__ico--flaticon">
                        <x-ui.flaticon name="student" class="sl-flaticon--landing" />
                    </div>
                    <h3>Intelligence collective</h3>
                    <p>Notez, commentez et améliorez les documents de la promo. Les meilleures fiches remontent grâce aux votes de la communauté.</p>
                </article>
                <article class="sl-landing-value sl-landing-value--ai">
                    <div class="sl-landing-value__ico sl-landing-value__ico--flaticon">
                        <x-ui.flaticon name="idea" class="sl-flaticon--landing" />
                    </div>
                    <h3>IA intégrée</h3>
                    <p>Résumés automatiques, recherche intelligente et recommandations personnalisées selon votre filière et vos révisions du moment.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="sl-landing-trust" id="trust">
        <div class="sl-landing-wrap py-8">
            <div class="sl-landing-sec-head mb-6">
                <span class="sl-landing-eyebrow">Confiance &amp; sécurité</span>
                <h2>Un espace fermé, fiable et personnel</h2>
            </div>

            <div class="sl-landing-trust-grid">
                <div class="sl-landing-trust-item">
                    <div class="sl-landing-trust-ico bg-primary-soft text-primary">
                        <x-ui.icon name="shield" class="h-[18px] w-[18px]" />
                    </div>
                    <div>
                        <h4>Accès vérifié uniquement</h4>
                        <p>Seuls les comptes confirmés par une adresse @hestim.ma peuvent consulter et déposer des ressources.</p>
                    </div>
                </div>
                <div class="sl-landing-trust-item">
                    <div class="sl-landing-trust-ico bg-success-soft text-success">
                        <x-ui.icon name="lock" class="h-[18px] w-[18px]" />
                    </div>
                    <div>
                        <h4>Documents sécurisés</h4>
                        <p>Vos fichiers sont stockés de façon chiffrée et restent strictement réservés à la communauté HESTIM.</p>
                    </div>
                </div>
                <div class="sl-landing-trust-item">
                    <div class="sl-landing-trust-ico bg-warning-soft text-warning">
                        <x-ui.icon name="sparkles" class="h-[18px] w-[18px]" />
                    </div>
                    <div>
                        <h4>Recommandations personnalisées</h4>
                        <p>L'IA vous suggère les ressources les plus utiles à votre filière et à vos prochains examens.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="sl-landing-sec">
        <div class="sl-landing-wrap">
            <div class="sl-landing-cta">
                <h2>Prêt à rejoindre votre promo ?</h2>
                <p>Connectez-vous en quelques secondes avec votre email institutionnel.</p>
                @auth
                    <x-ui.button href="{{ route('dashboard') }}" variant="primary" size="lg" class="sl-landing-cta__btn">
                        <x-ui.icon name="library" class="h-4 w-4" />
                        Accéder au tableau de bord
                    </x-ui.button>
                @else
                    <x-ui.button href="{{ route('login') }}" variant="primary" size="lg" class="sl-landing-cta__btn">
                        <x-ui.icon name="mail" class="h-4 w-4" />
                        Se connecter avec mon email @hestim.ma
                    </x-ui.button>
                @endauth
            </div>
        </div>
    </section>

    <footer class="sl-landing-footer">
        <div class="sl-landing-wrap sl-landing-footer__inner">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                <div class="sl-brand-mark" style="width:30px;height:30px;font-size:14px;border-radius:9px">S</div>
                <span class="text-body font-bold tracking-tight">StudyLib</span>
            </a>
            <nav class="flex flex-wrap gap-6" aria-label="Pied de page">
                <a href="#features" class="sl-landing-footer-link">Fonctionnalités</a>
                <a href="#trust" class="sl-landing-footer-link">Sécurité</a>
                <a href="#stats" class="sl-landing-footer-link">Communauté</a>
                <a href="#" class="sl-landing-footer-link">Contact</a>
            </nav>
            <span class="sl-landing-footer__copy">© {{ date('Y') }} StudyLib · Communauté HESTIM</span>
            <x-ui.flaticon-attribution class="sl-landing-footer__attrib" />
        </div>
    </footer>
</x-layouts.marketing>

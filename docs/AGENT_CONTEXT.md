# StudyLib — Contexte agent (source de vérité)

> Document de référence pour les agents IA travaillant sur ce dépôt.
> En cas de conflit avec une supposition non documentée ici, **ce fichier et le code existant priment**.

Dernière mise à jour : 2026-06-06

---

## 1. Vision produit

**StudyLib** est une plateforme collaborative pour les étudiants **HESTIM** (Maroc).

Objectifs :

- Centraliser les ressources pédagogiques (cours, examens, TD, TP)
- Partager les retours de stages
- Proposer des projets CV
- Fournir des recommandations IA (Claude) et YouTube
- Gérer les événements de l'école
- Modérer le contenu (administration)

Contrainte métier clé : authentification réservée aux emails **`@hestim.ma`**.

---

## 2. Stack technique (imposée)

| Couche | Technologie |
|---|---|
| Backend | Laravel 11 (cible projet), PHP 8.3, PSR-12 |
| Frontend | Blade, Tailwind CSS, Alpine.js, Livewire |
| Base de données | **MySQL 8** (conteneur Docker) |
| Cache / sessions / queues | Redis |
| Recherche | Meilisearch |
| Fichiers | MinIO (disque Laravel `minio`) |
| IA | Claude API |
| Vidéo | YouTube Data API v3 |
| Infra | Docker Compose |

> **État actuel du dépôt** : le squelette backend est en place. Les controllers renvoient du **JSON** en attendant les vues Blade. Le fichier `.env` local peut encore pointer vers SQLite ; la cible production/dev est MySQL via Docker. Docker Compose n'est pas encore présent dans le dépôt au moment de cette rédaction.

> **Note composer** : `composer.json` peut afficher une version Laravel plus récente que la cible « Laravel 11 » des règles projet. Respecter les conventions Laravel 11+ documentées ici.

---

## 3. Règles d'architecture (obligatoires)

### Clean Architecture / SOLID

```
HTTP Request
  → Controller      (orchestration, authorize, délégation)
  → Form Request    (validation des entrées)
  → Service         (logique métier — SEUL endroit autorisé)
  → Repository      (accès données via interface)
  → Model Eloquent
  → MySQL
```

### Interdictions strictes

| Ne jamais mettre de logique métier dans | Où la mettre |
|---|---|
| Blade | `app/Services/` |
| Composants Livewire | `app/Services/` |
| Controllers | `app/Services/` |

### Autres règles

- Validation : Form Requests + Policies Laravel
- `declare(strict_types=1);` sur les fichiers PHP applicatifs
- Formatage : `vendor/bin/pint` doit passer (`pint --test`)
- FK, index, soft deletes quand pertinent
- Pas de duplication de données inutile
- **Un seul module métier modifié à la fois** (workflow incrémental)
- Avant modification : lire `docs/`, identifier impacts, proposer un plan si demandé

---

## 4. Arborescence backend (existant)

```
app/
├── Enums/
│   ├── AiKind.php              (project, document, study_path, other)
│   ├── DocumentStatus.php      (pending, approved, rejected)
│   ├── DocumentType.php        (cours, examen, td, tp)
│   ├── IdeaSource.php          (student, ai)
│   ├── StudyLevel.php          (l1, l2, l3, m1, m2)
│   └── UserRole.php            (student, admin)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/              (EventController, ModerationController)
│   │   ├── Auth/               (RegisteredUserController, AuthenticatedSessionController)
│   │   └── …                   (14 controllers métier)
│   ├── Middleware/
│   │   ├── EnsureHestimEmail.php
│   │   └── EnsureUserIsAdmin.php   (alias route: admin)
│   └── Requests/               (Auth, Document, Event, Internship, Project, Ai, Admin, Profile)
├── Livewire/                   (vide — à créer au besoin)
├── Models/                     (13 modèles, UUID, HasUuids)
├── Policies/                   (Document, Event, InternshipReview, Notification, ProjectIdea, User)
├── Providers/
│   ├── AppServiceProvider.php      (enregistrement Gate::policy)
│   └── RepositoryServiceProvider.php
├── Repositories/
│   ├── Contracts/              (14 interfaces)
│   └── Eloquent/               (BaseRepository + 13 implémentations)
├── Rules/HestimEmail.php
└── Services/                   (15 services — voir section 6)

database/
├── migrations/                 (15 migrations — voir section 5)
├── factories/                  (User, Filiere, Module, Document, Company)
└── seeders/                    (DatabaseSeeder, FiliereSeeder)

routes/
├── web.php                     (routes applicatives)
└── auth.php                    (register, login, logout)
```

---

## 5. Modèle de données (MySQL 8)

Clés primaires : **UUID** (`HasUuids` côté Laravel).

### Tables et relations

| Table | Description | Soft delete |
|---|---|---|
| `filieres` | Filières HESTIM (GI, GC, GIND, MGT en seeder) | Non |
| `users` | Étudiants + admins, email @hestim.ma | Oui |
| `modules` | Modules par filière + semestre | Non |
| `companies` | Entreprises de stage | Non |
| `documents` | Fichiers pédagogiques (MinIO) | Oui |
| `document_ratings` | Notes 1-5, unique (user, document) | Non |
| `document_downloads` | Journal des téléchargements | Non |
| `internship_reviews` | Avis de stages | Oui |
| `project_ideas` | Idées projets CV (source student/ai) | Oui |
| `events` | Événements école | Oui |
| `notifications` | Notifications utilisateur (JSON `data`) | Non |
| `youtube_recommendations` | Vidéos par module | Non |
| `ai_recommendations` | Historique suggestions Claude | Non |

### Enums métier (PHP + colonnes string en BDD)

- **DocumentType** : `cours`, `examen`, `td`, `tp`
- **DocumentStatus** : `pending`, `approved`, `rejected` (modération admin)
- **UserRole** : `student`, `admin`
- **StudyLevel** : `l1`, `l2`, `l3`, `m1`, `m2`
- **IdeaSource** : `student`, `ai`
- **AiKind** : `project`, `document`, `study_path`, `other`

### Compteurs dénormalisés sur `documents`

- `downloads_count`, `ratings_count`, `avg_rating` (sync via `RatingService` / repository)

### Ordre des migrations

1. `0000_01_01_000000_create_filieres_table`
2. `0001_01_01_000000_create_users_table` (+ sessions, password_reset_tokens)
3. `2026_06_06_000001_create_modules_table`
4. `2026_06_06_000002_create_companies_table`
5. `2026_06_06_000003_create_documents_table`
6. `2026_06_06_000004_create_document_ratings_table`
7. `2026_06_06_000005_create_document_downloads_table`
8. `2026_06_06_000006_create_internship_reviews_table`
9. `2026_06_06_000007_create_project_ideas_table`
10. `2026_06_06_000008_create_events_table`
11. `2026_06_06_000009_create_notifications_table`
12. `2026_06_06_000010_create_youtube_recommendations_table`
13. `2026_06_06_000011_create_ai_recommendations_table`

Diagramme ERD de référence (HTML) : `docs/diagramme/schema_bdd.html`

---

## 6. Services métier (app/Services/)

| Service | Rôle |
|---|---|
| `AuthService` | Inscription, validation domaine @hestim.ma |
| `ProfileService` | Mise à jour profil, avatar (disque `public`) |
| `FiliereService` | Liste filières |
| `ModuleService` | Modules par filière |
| `DocumentService` | Upload MinIO, listing, URL temporaire, suppression |
| `RatingService` | Notation + sync agrégats |
| `DownloadService` | Enregistrement téléchargement + incrément compteur |
| `ModerationService` | File modération, approve/reject |
| `CompanyService` | Find or create entreprise |
| `InternshipReviewService` | CRUD avis stages |
| `ProjectIdeaService` | CRUD projets CV |
| `EventService` | CRUD événements |
| `NotificationService` | Liste / marquer lu |
| `ClaudeService` | Appels API Claude + persistance `ai_recommendations` |
| `YouTubeService` | Fetch/cache recommandations YouTube par module |

---

## 7. Routes (état actuel)

### Auth (`routes/auth.php`)

| Méthode | URI | Nom |
|---|---|---|
| POST | `/register` | `register` |
| POST | `/login` | `login` |
| POST | `/logout` | `logout` |

### Web (`routes/web.php`)

| Méthode | URI | Nom | Auth |
|---|---|---|---|
| GET | `/` | `home` | Public |
| GET | `/filieres` | `filieres.index` | Public |
| GET | `/filieres/{filiere}/modules` | `filieres.modules` | Public |
| GET | `/dashboard` | `dashboard` | auth + verified |
| GET/PATCH | `/profile` | `profile.show` / `profile.update` | auth + verified |
| CRUD | `/documents` | `documents.*` | auth + verified |
| POST | `/documents/{document}/ratings` | `documents.ratings.store` | auth + verified |
| POST | `/documents/{document}/download` | `documents.download` | auth + verified |
| GET | `/modules/{module}/youtube` | `modules.youtube` | auth + verified |
| GET/POST | `/internship-reviews` | `internship-reviews.*` | auth + verified |
| GET/POST | `/project-ideas` | `project-ideas.*` | auth + verified |
| GET | `/events` | `events.index` | auth + verified |
| POST | `/ai/suggestions` | `ai.suggestions` | auth + verified |
| GET/PATCH | `/notifications` | `notifications.*` | auth + verified |
| Admin | `/admin/moderation/documents` | `admin.moderation.*` | auth + verified + admin |
| Admin | `/admin/events` | `admin.events.*` | auth + verified + admin |

Middleware `admin` → `EnsureUserIsAdmin` (vérifie `User::isAdmin()`).

---

## 8. Vues Blade — règles de placement (CRITIQUE)

### Principe

**Toutes les vues de production vivent exclusivement sous `resources/views/`.**

| Emplacement | Autorisé | Rôle |
|---|---|---|
| `resources/views/` | **OUI** | Seul dossier pour les vues Blade de l'application |
| `resources/views/layouts/` | **OUI** | Layouts (`app.blade.php`, `guest.blade.php`, `admin.blade.php`) |
| `resources/views/components/` | **OUI** | Composants Blade anonymes / class-based |
| `resources/views/livewire/` | **OUI** | Vues des composants Livewire (convention Laravel) |
| `resources/views/pages/` | **OUI** | Pages finales par écran |
| `resources/views/partials/` | **OUI** | Fragments réutilisables (header, sidebar, footer) |
| `app/Livewire/` | **OUI** | Classes PHP Livewire uniquement (pas de `.blade.php` ici) |
| `app/Http/Controllers/` | **NON** | Pas de HTML, pas de Blade |
| `public/` | **NON** | Pas de templates Blade (assets statiques seulement) |
| `docs/prototype/` | **NON** | Maquettes HTML de référence — **ne pas copier comme vues finales** |
| `docs/design-system/` | **NON** | Design system HTML — référence visuelle uniquement |
| Racine du projet ou tout autre dossier | **NON** | Interdit pour les vues |

### Arborescence cible des vues (à respecter)

```
resources/views/
├── layouts/
│   ├── app.blade.php           # Layout principal authentifié
│   ├── guest.blade.php         # Layout login / register
│   └── admin.blade.php         # Layout administration
├── components/
│   └── …                       # Boutons, cards, badges (design system)
├── partials/
│   ├── header.blade.php
│   ├── sidebar.blade.php
│   └── footer.blade.php
├── livewire/
│   └── …                       # Un fichier .blade.php par composant Livewire
└── pages/
    ├── landing.blade.php                    ← docs/prototype/StudyLib Landing.html
    ├── auth/
    │   ├── login.blade.php                  ← docs/prototype/StudyLib Login.html
    │   └── register.blade.php
    ├── dashboard/
    │   └── index.blade.php                  ← docs/prototype/StudyLib Dashboard.html
    ├── documents/
    │   ├── index.blade.php                  ← docs/prototype/StudyLib Bibliothèque.html
    │   └── show.blade.php                   ← docs/prototype/StudyLib Détail Document.html
    ├── internship-reviews/
    │   └── index.blade.php                  ← docs/prototype/StudyLib Stages.html
    ├── project-ideas/
    │   └── index.blade.php                  ← docs/prototype/StudyLib Projets CV.html
    ├── events/
    │   └── index.blade.php                  ← docs/prototype/StudyLib Événements.html
    ├── profile/
    │   └── show.blade.php                   ← docs/prototype/StudyLib Profil.html
    └── admin/
        └── index.blade.php                    ← docs/prototype/StudyLib Admin.html
```

### Correspondance prototype → vue Blade

| Maquette (référence ONLY) | Vue Blade (production) | Route Laravel |
|---|---|---|
| `docs/prototype/StudyLib Landing.html` | `resources/views/pages/landing.blade.php` | `home` |
| `docs/prototype/StudyLib Login.html` | `resources/views/pages/auth/login.blade.php` | `login` (GET à ajouter) |
| `docs/prototype/StudyLib Dashboard.html` | `resources/views/pages/dashboard/index.blade.php` | `dashboard` |
| `docs/prototype/StudyLib Bibliothèque.html` | `resources/views/pages/documents/index.blade.php` | `documents.index` |
| `docs/prototype/StudyLib Détail Document.html` | `resources/views/pages/documents/show.blade.php` | `documents.show` |
| `docs/prototype/StudyLib Stages.html` | `resources/views/pages/internship-reviews/index.blade.php` | `internship-reviews.index` |
| `docs/prototype/StudyLib Projets CV.html` | `resources/views/pages/project-ideas/index.blade.php` | `project-ideas.index` |
| `docs/prototype/StudyLib Événements.html` | `resources/views/pages/events/index.blade.php` | `events.index` |
| `docs/prototype/StudyLib Profil.html` | `resources/views/pages/profile/show.blade.php` | `profile.show` |
| `docs/prototype/StudyLib Admin.html` | `resources/views/pages/admin/index.blade.php` | `admin.moderation.index` |

### Règles UI (maquettes)

- **Source visuelle** : `docs/prototype/` + `docs/design-system/StudyLib Design System.html`
- **Tokens CSS** : `docs/prototype/tokens.css` (à porter dans Tailwind / `resources/css/app.css`)
- **Ne jamais** inventer d'écrans, champs ou parcours non présents dans les maquettes
- **Mobile first**, responsive, accessibilité WCAG AA
- **Ne jamais** utiliser le caractère `——` (tiret cadratin double) dans l'UI
- Les controllers devront retourner `view('pages.xxx')` et non du JSON une fois le frontend branché

### État actuel des vues

- **Design system Tailwind** : `resources/css/app.css` (tokens + classes `sl-*` calquées sur `docs/prototype/tokens.css` et le Design System)
- **Layouts** : `resources/views/components/layouts/{app,guest,admin}.blade.php` (usage : `<x-layouts.app>`)
- **Composants Blade** : `resources/views/components/ui/*`
- **Composants Livewire** : `app/Livewire/` + `resources/views/livewire/`
- **Page exemple** : `resources/views/pages/auth/login.blade.php` (GET `/login`)
- **Livewire v4** installé (`composer require livewire/livewire`)
- Les controllers renvoient encore du JSON pour les autres écrans (à brancher progressivement)

---

## 9. Configuration & intégrations

### Fichiers (`config/`)

- `filesystems.php` : disque **`minio`** (S3-compatible, path-style)
- `services.php` : clés `claude` et `youtube`

### Variables d'environnement attendues (cible Docker)

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=studylib
DB_USERNAME=…
DB_PASSWORD=…

REDIS_HOST=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

MINIO_ENDPOINT=http://minio:9000
MINIO_ACCESS_KEY=…
MINIO_SECRET_KEY=…
MINIO_BUCKET=studylib

MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=…

CLAUDE_API_KEY=…
YOUTUBE_API_KEY=…
```

### Seeders de test

- `FiliereSeeder` : GI, GC, GIND, MGT
- `DatabaseSeeder` : `admin@hestim.ma` (admin), `etudiant@hestim.ma` (student), mot de passe factory : `password`

---

## 10. Sécurité

- Email `@hestim.ma` : règle `HestimEmail`, middleware `EnsureHestimEmail`, méthode `AuthService::emailBelongsToHestim()`
- Policies sur toutes les actions sensibles
- Documents : visibles si `approved`, ou auteur, ou admin
- Modération : `DocumentPolicy::moderate()` réservée admin
- Fichiers : stockage MinIO, URLs temporaires pour téléchargement
- Ne jamais exposer clés API ou chemins internes MinIO au client

---

## 11. Backlog MVP (priorisation)

### P0 — Indispensable MVP

- Authentification @hestim.ma (register, login, logout)
- Vues : Landing, Login, Dashboard
- Bibliothèque : listing documents par module/type, upload, détail, téléchargement
- Modération admin : approve / reject documents
- Filières + modules (données de référence)

### P1 — MVP enrichi

- Notation documents
- Avis de stages + entreprises
- Projets CV
- Événements (lecture étudiant, CRUD admin)
- Profil utilisateur

### P2 — Post-MVP

- Suggestions IA (Claude)
- Recommandations YouTube
- Notifications
- Recherche Meilisearch full-text
- Docker Compose complet + Redis queues

---

## 12. Ce qui n'existe PAS encore (ne pas halluciner)

- Docker Compose dans le dépôt
- Vues Blade métier (sauf `welcome.blade.php` par défaut)
- Composants Livewire
- Routes GET pour login/register (seulement POST auth actuellement)
- Intégration Meilisearch implémentée
- Tests feature complets par module
- Package Breeze/Fortify installé

---

## 13. Commandes utiles

```bash
# Formatage PSR-12
vendor/bin/pint

# Vérifier routes
php artisan route:list

# Migrations (MySQL requis)
php artisan migrate
php artisan db:seed

# Vérifier le squelette
vendor/bin/pint --test
php artisan route:list
```

---

## 14. Fichiers de référence à lire avant d'agir

| Fichier | Contenu |
|---|---|
| `.cursor/rules/studylib.mdc` | Règles IA permanentes |
| `docs/AGENT_CONTEXT.md` | Ce document |
| `docs/prototype/*.html` | Maquettes écran par écran |
| `docs/design-system/StudyLib Design System.html` | Tokens, composants |
| `docs/diagramme/schema_bdd.html` | ERD |
| `routes/web.php` + `routes/auth.php` | Routes réelles |
| `app/Providers/RepositoryServiceProvider.php` | Bindings repository |

---

## 15. Checklist agent avant toute PR

- [ ] Lu la maquette correspondante dans `docs/prototype/`
- [ ] Vue créée **uniquement** sous `resources/views/` à l'emplacement défini section 8
- [ ] Logique métier dans `app/Services/`, pas dans Controller/Blade/Livewire
- [ ] Form Request pour les entrées utilisateur
- [ ] Policy vérifiée si action protégée
- [ ] Un seul module métier touché
- [ ] `vendor/bin/pint --test` OK
- [ ] Pas de champs/écrans inventés

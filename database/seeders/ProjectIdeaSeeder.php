<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\IdeaSource;
use App\Enums\ProjectDifficulty;
use App\Enums\StudyLevel;
use App\Models\Filiere;
use App\Models\ProjectIdea;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Base de connaissances de 100 idées de projets pour le moteur de recommandation
 * (cf. App\Services\Recommendation) : 4 filières × 5 niveaux × 5 idées.
 *
 * Chaque idée porte les signaux exploités par le moteur de matching — filière,
 * niveau conseillé, modules visés et tags de compétences/technologies — ainsi
 * que des métadonnées de cadrage (difficulté, durée estimée).
 */
class ProjectIdeaSeeder extends Seeder
{
    /** @var array<string, Tag> */
    private array $tags = [];

    public function run(): void
    {
        $filieres = Filiere::query()->get()->keyBy('code');

        foreach (self::projects() as $definition) {
            $filiere = $filieres->get($definition['filiere']);

            $project = ProjectIdea::query()->updateOrCreate(
                ['title' => $definition['title']],
                [
                    'filiere_id' => $filiere?->id,
                    'description' => $definition['description'],
                    'level' => $definition['level']->value,
                    'difficulty' => $definition['difficulty']->value,
                    'estimated_weeks' => $definition['weeks'],
                    'source' => IdeaSource::Student->value,
                ],
            );

            $project->tags()->sync(
                collect($definition['tags'])->map(fn (string $name): string => $this->tag($name)->id),
            );
        }
    }

    private function tag(string $name): Tag
    {
        return $this->tags[$name] ??= Tag::query()->firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name],
        );
    }

    /**
     * @return list<array{filiere: string, level: StudyLevel, difficulty: ProjectDifficulty, weeks: int, title: string, description: string, tags: list<string>}>
     */
    private static function projects(): array
    {
        return [
            // ───────────────────────── Génie Informatique (GI) ─────────────────────────
            [
                'filiere' => 'GI', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 2,
                'title' => 'Site vitrine responsive pour une association étudiante',
                'description' => "Concevoir et développer un site vitrine responsive présentant les activités, les membres et les événements d'une association étudiante, avec un formulaire de contact fonctionnel.",
                'tags' => ['HTML', 'CSS', 'JavaScript'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 2,
                'title' => 'Calculatrice scientifique en ligne',
                'description' => 'Développer une calculatrice scientifique interactive dans le navigateur, gérant les opérations de base, les fonctions trigonométriques et un historique des calculs.',
                'tags' => ['HTML', 'CSS', 'JavaScript'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 3,
                'title' => 'Gestionnaire de tâches personnel',
                'description' => 'Créer une application web simple permettant de créer, organiser et suivre des tâches quotidiennes, avec catégories et rappels.',
                'tags' => ['JavaScript', 'HTML', 'CSS'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 2,
                'title' => 'Jeu du pendu en ligne de commande',
                'description' => "Implémenter le jeu du pendu en mode console, avec gestion des essais, des thèmes de mots et un système de score.",
                'tags' => ['Python'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 2,
                'title' => "Convertisseur d'unités multidomaine",
                'description' => "Développer un convertisseur d'unités (longueur, masse, température, devises) avec une interface simple et intuitive.",
                'tags' => ['JavaScript', 'HTML', 'CSS'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 4,
                'title' => 'Application de gestion de bibliothèque personnelle',
                'description' => 'Construire une application web de catalogage de bibliothèque personnelle, avec recherche, emprunts fictifs et statistiques de lecture.',
                'tags' => ['PHP', 'MySQL', 'Bootstrap'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 5,
                'title' => 'Blog collaboratif avec authentification',
                'description' => "Développer un blog multi-auteurs avec inscription, gestion de profils, rédaction d'articles et système de commentaires modérés.",
                'tags' => ['Laravel', 'MySQL', 'Bootstrap'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 5,
                'title' => "API REST de gestion d'inventaire",
                'description' => "Concevoir une API REST sécurisée pour la gestion des stocks d'un commerce, avec authentification par token et documentation interactive.",
                'tags' => ['Node.js', 'Express', 'MongoDB', 'REST API'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 6,
                'title' => 'Application mobile de prise de notes',
                'description' => 'Créer une application mobile de prise de notes avec organisation par carnets, étiquettes et synchronisation locale hors ligne.',
                'tags' => ['Flutter', 'SQLite'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 5,
                'title' => "Système de réservation de salles d'étude",
                'description' => 'Développer une plateforme permettant aux étudiants de réserver des salles de travail selon leur disponibilité, avec calendrier et notifications.',
                'tags' => ['PHP', 'MySQL', 'Bootstrap'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 8,
                'title' => 'Plateforme de mise en relation étudiants-tuteurs',
                'description' => "Concevoir une plateforme mettant en relation étudiants et tuteurs par matière, avec prise de rendez-vous, messagerie et système d'évaluation.",
                'tags' => ['Laravel', 'Vue.js', 'PostgreSQL'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 8,
                'title' => 'Application de covoiturage pour étudiants',
                'description' => "Développer une application mobile de covoiturage entre étudiants d'un même campus, avec géolocalisation, trajets récurrents et messagerie intégrée.",
                'tags' => ['React Native', 'Node.js', 'MongoDB'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 7,
                'title' => 'Système de gestion de présence par QR code',
                'description' => 'Mettre en place un système de pointage des présences en cours via génération et scan de QR codes, avec tableau de bord pour les enseignants.',
                'tags' => ['Laravel', 'React', 'MySQL'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 9,
                'title' => "Marketplace de vente d'occasion entre étudiants",
                'description' => "Construire une marketplace permettant aux étudiants d'acheter et de vendre des biens d'occasion, avec paiement en ligne et messagerie entre utilisateurs.",
                'tags' => ['Laravel', 'React', 'PostgreSQL', 'Stripe'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 6,
                'title' => 'Tableau de bord de suivi des dépenses personnelles',
                'description' => "Développer une application de suivi budgétaire personnel avec catégorisation automatique des dépenses, graphiques et objectifs d'épargne.",
                'tags' => ['Vue.js', 'Laravel', 'Chart.js'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 10,
                'title' => 'Système de recommandation de contenus pédagogiques basé sur des règles métier',
                'description' => "Concevoir un moteur de recommandation de documents et exercices pédagogiques fondé sur des règles métier (filière, niveau, historique), sans recours à l'IA générative.",
                'tags' => ['Laravel', 'PostgreSQL', 'Redis'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 12,
                'title' => 'Plateforme de visioconférence pédagogique',
                'description' => 'Développer une plateforme de visioconférence dédiée aux cours en ligne, avec partage d\'écran, tableau blanc collaboratif et enregistrement des sessions.',
                'tags' => ['WebRTC', 'Node.js', 'React'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 10,
                'title' => 'Application de gestion de projets agile type Kanban',
                'description' => "Créer un outil de gestion de projets collaboratif inspiré de Trello, avec tableaux Kanban, étiquettes, pièces jointes et notifications en temps réel.",
                'tags' => ['React', 'GraphQL', 'Node.js', 'PostgreSQL'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 12,
                'title' => 'Système de détection de plagiat de documents académiques',
                'description' => "Concevoir un outil d'analyse comparative de documents académiques permettant de détecter les similarités textuelles suspectes.",
                'tags' => ['Python', 'NLP', 'Flask'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 10,
                'title' => 'Pipeline CI/CD automatisé pour microservices',
                'description' => "Mettre en place une chaîne d'intégration et de déploiement continus pour une architecture de microservices conteneurisés, avec tests automatisés et déploiement progressif.",
                'tags' => ['Docker', 'Kubernetes', 'Jenkins'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 14,
                'title' => 'Plateforme de microservices pour la gestion universitaire',
                'description' => "Concevoir une architecture de microservices pour la gestion des inscriptions, des notes et des emplois du temps d'un établissement, orchestrée par conteneurs.",
                'tags' => ['Spring Boot', 'Docker', 'Kubernetes', 'PostgreSQL'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 14,
                'title' => "Système de détection de fraude par apprentissage automatique",
                'description' => 'Développer un modèle d\'apprentissage automatique capable d\'identifier des transactions ou comportements suspects à partir de données historiques.',
                'tags' => ['Python', 'Scikit-learn', 'Pandas'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 12,
                'title' => 'Moteur de recherche sémantique pour documents académiques',
                'description' => 'Construire un moteur de recherche capable d\'indexer et de retrouver des documents académiques par similarité sémantique plutôt que par simples mots-clés.',
                'tags' => ['Elasticsearch', 'Python', 'NLP'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 12,
                'title' => 'Architecture serverless pour notifications en temps réel',
                'description' => 'Concevoir une architecture serverless capable de traiter et de diffuser des notifications en temps réel à grande échelle.',
                'tags' => ['AWS Lambda', 'Node.js', 'DynamoDB'],
            ],
            [
                'filiere' => 'GI', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 14,
                'title' => 'Plateforme blockchain de certification de diplômes',
                'description' => 'Développer une plateforme permettant d\'émettre et de vérifier des diplômes numériques infalsifiables grâce à la blockchain.',
                'tags' => ['Solidity', 'Ethereum', 'React'],
            ],

            // ───────────────────────────── Génie Civil (GC) ─────────────────────────────
            [
                'filiere' => 'GC', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 2,
                'title' => "Maquette numérique d'un bâtiment résidentiel",
                'description' => "Réaliser la maquette numérique 3D d'un petit bâtiment résidentiel à des fins de présentation et de visualisation volumétrique.",
                'tags' => ['SketchUp'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 2,
                'title' => 'Calculateur de quantités de matériaux de construction',
                'description' => "Concevoir un outil de calcul automatique des quantités de matériaux nécessaires (béton, briques, peinture) à partir des dimensions d'un ouvrage.",
                'tags' => ['Excel VBA'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 3,
                'title' => "Plan d'aménagement d'un espace vert",
                'description' => "Élaborer le plan d'aménagement paysager d'un espace vert universitaire, intégrant cheminements, végétation et zones de repos.",
                'tags' => ['AutoCAD'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 2,
                'title' => 'Carnet de chantier numérique',
                'description' => "Concevoir un carnet de chantier numérique simple permettant de consigner quotidiennement l'avancement, les incidents et les ressources mobilisées.",
                'tags' => ['Excel'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 3,
                'title' => 'Étude comparative de matériaux de construction écologiques',
                'description' => "Mener une étude comparative de matériaux de construction durables (impact environnemental, coût, performance) et restituer les résultats sous forme de rapport et de présentation.",
                'tags' => ['Excel', 'Power BI'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 6,
                'title' => "Modélisation 3D BIM d'un bâtiment résidentiel",
                'description' => "Créer la maquette numérique BIM d'un bâtiment résidentiel intégrant les aspects architecturaux et structurels de base.",
                'tags' => ['Revit', 'BIM'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 6,
                'title' => 'Calcul de structures simples par éléments finis',
                'description' => 'Modéliser et analyser le comportement de structures simples (poutres, portiques) à l\'aide de la méthode des éléments finis.',
                'tags' => ['MATLAB'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 6,
                'title' => 'Application de suivi de chantier mobile',
                'description' => "Développer une application mobile permettant aux conducteurs de travaux de suivre l'avancement d'un chantier, de prendre des photos horodatées et de signaler des anomalies.",
                'tags' => ['Flutter', 'Firebase'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 5,
                'title' => "Plan topographique numérique d'un terrain",
                'description' => "Réaliser le relevé et la modélisation topographique numérique d'un terrain à partir de données de levé, en vue d'un projet d'aménagement.",
                'tags' => ['QGIS', 'AutoCAD'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 5,
                'title' => "Simulateur de dimensionnement de réseaux d'assainissement",
                'description' => "Concevoir un outil de calcul assistant le dimensionnement de réseaux d'assainissement selon les débits et la pente du terrain.",
                'tags' => ['Excel VBA', 'MATLAB'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 8,
                'title' => 'Maquette BIM collaborative pour un projet de bâtiment',
                'description' => "Élaborer une maquette numérique BIM collaborative intégrant les disciplines architecture, structure et fluides, avec détection des conflits.",
                'tags' => ['Revit', 'BIM', 'Navisworks'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 7,
                'title' => 'Application de gestion de planning de chantier',
                'description' => 'Développer un outil de planification et de suivi des tâches de chantier, avec diagramme de Gantt, jalons et alertes de retard.',
                'tags' => ['MS Project', 'Power Apps'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 8,
                'title' => 'Outil de calcul de structures en béton armé',
                'description' => 'Concevoir un outil assistant le dimensionnement d\'éléments de structure en béton armé conformément aux normes en vigueur.',
                'tags' => ['MATLAB', 'Robot Structural Analysis'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 9,
                'title' => 'Système de suivi environnemental de chantier par capteurs',
                'description' => 'Mettre en place un système de capteurs connectés mesurant en continu le bruit, la poussière et les vibrations sur un chantier, avec restitution sur tableau de bord.',
                'tags' => ['Arduino', 'Raspberry Pi', 'IoT'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 7,
                'title' => "Plateforme de gestion documentaire pour bureaux d'études",
                'description' => "Développer une plateforme centralisant le classement, le partage et le suivi des versions des documents techniques d'un bureau d'études.",
                'tags' => ['Laravel', 'PostgreSQL'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 10,
                'title' => 'Optimisation de la planification de chantier par algorithmes',
                'description' => "Appliquer des algorithmes d'optimisation à la planification des ressources et des tâches d'un chantier afin de réduire les délais et les coûts.",
                'tags' => ['Python', 'Primavera P6'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 12,
                'title' => 'Modélisation BIM 4D/5D pour le suivi de coûts et délais',
                'description' => "Enrichir une maquette BIM avec les dimensions temporelle et financière afin de simuler l'avancement et les coûts d'un projet de construction.",
                'tags' => ['Revit', 'Navisworks', 'BIM'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 11,
                'title' => 'Système de surveillance structurelle par capteurs connectés',
                'description' => 'Concevoir un système de capteurs permettant de surveiller en continu les déformations et vibrations d\'un ouvrage et de détecter les anomalies structurelles.',
                'tags' => ['IoT', 'Arduino', 'Power BI'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 12,
                'title' => 'Outil de simulation de performance énergétique de bâtiments',
                'description' => "Développer un outil de simulation thermique et énergétique permettant d'évaluer et d'optimiser la consommation d'un bâtiment.",
                'tags' => ['MATLAB', 'EnergyPlus'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 10,
                'title' => 'Plateforme collaborative de gestion de projets de construction',
                'description' => "Construire une plateforme web centralisant la coordination entre les différents intervenants d'un projet de construction (maîtrise d'œuvre, entreprises, clients).",
                'tags' => ['Laravel', 'React', 'PostgreSQL'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 14,
                'title' => "Jumeau numérique d'une infrastructure pour la maintenance prédictive",
                'description' => "Concevoir un jumeau numérique d'une infrastructure existante, alimenté par des capteurs IoT, pour anticiper les besoins de maintenance.",
                'tags' => ['BIM', 'IoT', 'Power BI'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 12,
                'title' => 'Système d\'aide à la décision pour le choix de matériaux durables',
                'description' => 'Développer un outil d\'aide à la décision multicritère permettant de comparer des matériaux de construction selon leur impact environnemental, leur coût et leur performance.',
                'tags' => ['Python', 'Pandas', 'Power BI'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 13,
                'title' => 'Optimisation de la logistique de chantier par modélisation mathématique',
                'description' => 'Modéliser et optimiser les flux logistiques d\'un grand chantier (approvisionnement, stockage, acheminement) à l\'aide de modèles mathématiques.',
                'tags' => ['MATLAB', 'Python'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 14,
                'title' => 'Plateforme intégrée BIM-GMAO pour la maintenance d\'infrastructures',
                'description' => 'Concevoir une plateforme reliant les maquettes BIM aux outils de gestion de maintenance assistée par ordinateur pour le suivi du cycle de vie des infrastructures.',
                'tags' => ['Revit', 'BIM', 'ERP'],
            ],
            [
                'filiere' => 'GC', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 14,
                'title' => 'Étude de la résilience d\'infrastructures face aux risques climatiques',
                'description' => 'Mener une étude de modélisation visant à évaluer et améliorer la résilience d\'infrastructures existantes face aux aléas climatiques (inondations, fortes chaleurs).',
                'tags' => ['MATLAB', 'QGIS', 'Python'],
            ],

            // ─────────────────────────── Génie Industriel (GIND) ───────────────────────────
            [
                'filiere' => 'GIND', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 2,
                'title' => "Cartographie d'un processus de production",
                'description' => "Réaliser la cartographie détaillée d'un processus de production existant afin d'identifier les étapes, les flux et les points de blocage.",
                'tags' => ['Excel', 'Visio'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 2,
                'title' => "Calculateur de coûts de production d'un atelier",
                'description' => "Concevoir un outil de calcul automatique des coûts de production (matières, main-d'œuvre, équipements) pour un petit atelier.",
                'tags' => ['Excel VBA'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 3,
                'title' => 'Étude 5S appliquée à un poste de travail',
                'description' => 'Mener une démarche d\'amélioration 5S sur un poste de travail (trier, ranger, nettoyer, standardiser, maintenir) et en mesurer les bénéfices.',
                'tags' => ['Excel', 'Visio'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 3,
                'title' => "Maquette d'un convoyeur automatisé",
                'description' => 'Concevoir et réaliser la maquette fonctionnelle d\'un petit convoyeur automatisé piloté par microcontrôleur et capteurs.',
                'tags' => ['Arduino', 'IoT'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 2,
                'title' => 'Tableau de bord basique de suivi de production',
                'description' => "Développer un tableau de bord simple présentant les indicateurs clés de production (cadence, rebuts, temps d'arrêt) d'un atelier.",
                'tags' => ['Excel', 'Power BI'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 5,
                'title' => "Application de gestion des stocks d'un atelier",
                'description' => 'Développer une application web de suivi des stocks de matières premières et de produits finis, avec alertes de seuil et historique des mouvements.',
                'tags' => ['Laravel', 'MySQL', 'Bootstrap'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 6,
                'title' => "Simulation d'une chaîne de production",
                'description' => "Modéliser et simuler le fonctionnement d'une chaîne de production afin d'identifier les goulets d'étranglement et de tester des scénarios d'amélioration.",
                'tags' => ['Arena', 'Witness'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 7,
                'title' => 'Système de contrôle qualité par vision industrielle',
                'description' => "Concevoir un système de détection automatique de défauts visuels sur une ligne de production à l'aide de la vision par ordinateur.",
                'tags' => ['Python', 'OpenCV', 'Computer Vision'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 6,
                'title' => "Optimisation d'un planning de production par algorithmes simples",
                'description' => "Développer un outil d'aide à la planification de production permettant de répartir les tâches sur les ressources disponibles afin de minimiser les retards.",
                'tags' => ['Python', 'Excel'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 5,
                'title' => "Application mobile de signalement d'anomalies en production",
                'description' => 'Créer une application mobile permettant aux opérateurs de signaler en temps réel les anomalies constatées sur la ligne de production, avec photos et géolocalisation.',
                'tags' => ['Flutter', 'Firebase'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 8,
                'title' => 'Système MES léger de suivi de production en temps réel',
                'description' => 'Développer un système léger de suivi de production en temps réel collectant les données des machines via des capteurs connectés et les restituant sur un tableau de bord.',
                'tags' => ['Node.js', 'React', 'MQTT', 'IoT'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 8,
                'title' => "Outil d'optimisation de la chaîne logistique",
                'description' => "Concevoir un outil d'aide à la décision permettant d'optimiser les flux logistiques (approvisionnement, stockage, distribution) d'une entreprise industrielle.",
                'tags' => ['Python', 'OR-Tools'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 8,
                'title' => 'Plateforme de gestion de maintenance préventive (GMAO)',
                'description' => "Développer une plateforme de gestion de maintenance assistée par ordinateur permettant de planifier les interventions préventives et de suivre l'historique des équipements.",
                'tags' => ['Laravel', 'PostgreSQL'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 7,
                'title' => 'Tableau de bord de pilotage industriel temps réel',
                'description' => "Concevoir un tableau de bord temps réel agrégeant les données de production issues de capteurs connectés pour piloter la performance d'un atelier.",
                'tags' => ['Power BI', 'IoT', 'SQL'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 8,
                'title' => 'Système de traçabilité des produits par RFID/QR code',
                'description' => 'Mettre en place un système de traçabilité des produits tout au long de la chaîne de production grâce à l\'identification par RFID ou QR code.',
                'tags' => ['Arduino', 'Raspberry Pi', 'Laravel'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 10,
                'title' => 'Optimisation de la supply chain par programmation linéaire',
                'description' => "Modéliser et résoudre des problèmes d'optimisation de la chaîne d'approvisionnement (transport, stockage, approvisionnement) à l'aide de la programmation linéaire.",
                'tags' => ['Python', 'PuLP', 'Excel'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 12,
                'title' => 'Système de maintenance prédictive par analyse de données capteurs',
                'description' => "Développer un modèle prédictif capable d'anticiper les pannes d'équipements industriels à partir de l'analyse de données issues de capteurs.",
                'tags' => ['Python', 'Pandas', 'Scikit-learn'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 12,
                'title' => "Jumeau numérique d'une ligne de production",
                'description' => 'Concevoir un jumeau numérique d\'une ligne de production permettant de simuler son fonctionnement et de tester des scénarios d\'optimisation sans interrompre la production réelle.',
                'tags' => ['Unity', 'IoT', 'Power BI'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 11,
                'title' => "Système d'ordonnancement intelligent de production",
                'description' => "Développer un système d'ordonnancement capable de proposer automatiquement des séquences de production optimisées selon les contraintes de l'atelier.",
                'tags' => ['Python', 'OR-Tools'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 10,
                'title' => 'Plateforme de pilotage Lean Six Sigma',
                'description' => "Concevoir une plateforme de suivi des projets d'amélioration continue Lean Six Sigma, intégrant indicateurs, analyses statistiques et plans d'action.",
                'tags' => ['Power BI', 'Minitab', 'SQL'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 14,
                'title' => 'Planification industrielle par apprentissage par renforcement',
                'description' => "Explorer l'usage de l'apprentissage par renforcement pour optimiser dynamiquement la planification de production d'un atelier complexe.",
                'tags' => ['Python', 'TensorFlow', 'OR-Tools'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 13,
                'title' => "Optimisation énergétique d'un site industriel",
                'description' => "Concevoir un système de suivi et d'optimisation de la consommation énergétique d'un site industriel à l'aide de capteurs connectés et de modèles d'analyse.",
                'tags' => ['Python', 'IoT', 'Power BI'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 14,
                'title' => 'Plateforme intégrée ERP-MES pour PME industrielles',
                'description' => "Développer une plateforme reliant la gestion d'entreprise (ERP) et le pilotage de production (MES) adaptée aux besoins des petites et moyennes entreprises industrielles.",
                'tags' => ['SAP', 'Laravel', 'PostgreSQL'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 14,
                'title' => 'Détection d\'anomalies de production par deep learning',
                'description' => "Concevoir un système de détection d'anomalies de production en temps réel à partir de modèles d'apprentissage profond appliqués à des données de capteurs et d'images.",
                'tags' => ['Python', 'TensorFlow', 'Computer Vision'],
            ],
            [
                'filiere' => 'GIND', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 14,
                'title' => 'Architecture Industrie 4.0 pour une usine pilote',
                'description' => "Concevoir l'architecture technique d'une usine pilote connectée intégrant capteurs IoT, automatisation et tableaux de bord décisionnels.",
                'tags' => ['IoT', 'SCADA', 'Power BI'],
            ],

            // ──────────────────────────────── Management (MGT) ────────────────────────────────
            [
                'filiere' => 'MGT', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 2,
                'title' => 'Étude de marché simplifiée pour un produit local',
                'description' => 'Réaliser une étude de marché simplifiée (enquête, analyse de la concurrence, segmentation) pour un produit ou service local.',
                'tags' => ['Excel', 'Google Forms'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 2,
                'title' => 'Plan de communication digitale pour une petite entreprise',
                'description' => 'Élaborer un plan de communication digitale (réseaux sociaux, contenu, calendrier éditorial) pour une petite entreprise locale.',
                'tags' => ['Canva', 'Google Analytics'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 2,
                'title' => 'Tableau de bord de suivi budgétaire personnel',
                'description' => "Concevoir un tableau de bord permettant de suivre ses revenus, dépenses et objectifs d'épargne mensuels.",
                'tags' => ['Excel'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 3,
                'title' => 'Business plan simplifié pour un projet entrepreneurial',
                'description' => "Rédiger un business plan simplifié pour un projet entrepreneurial étudiant, intégrant étude de marché, plan d'action et prévisions financières de base.",
                'tags' => ['Excel', 'Canva'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::L1, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 2,
                'title' => 'Sondage et analyse de satisfaction client',
                'description' => 'Concevoir et diffuser une enquête de satisfaction client, puis analyser et restituer les résultats sous forme de rapport synthétique.',
                'tags' => ['Google Forms', 'Excel'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Beginner, 'weeks' => 5,
                'title' => 'Application de facturation pour auto-entrepreneurs',
                'description' => "Développer une application web simple de création et de suivi de factures et devis destinée aux auto-entrepreneurs.",
                'tags' => ['Laravel', 'MySQL', 'Bootstrap'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 5,
                'title' => 'Plan marketing digital complet pour une marque fictive',
                'description' => 'Élaborer un plan marketing digital complet (positionnement, SEO, réseaux sociaux, indicateurs de performance) pour une marque fictive.',
                'tags' => ['Google Analytics', 'Canva'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 6,
                'title' => 'Mini-CRM de gestion de la relation client',
                'description' => 'Concevoir un outil léger de gestion de la relation client permettant de suivre les contacts, les opportunités commerciales et les échanges.',
                'tags' => ['Laravel', 'Vue.js', 'MySQL'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 5,
                'title' => 'Tableau de bord de pilotage commercial',
                'description' => 'Développer un tableau de bord agrégeant les indicateurs commerciaux (ventes, marges, taux de conversion) à partir de données issues de plusieurs sources.',
                'tags' => ['Power BI', 'Excel', 'SQL'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::L2, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 5,
                'title' => "Étude de faisabilité d'un projet de création d'entreprise",
                'description' => "Réaliser une étude de faisabilité complète (analyse SWOT, business model canvas, plan financier prévisionnel) pour un projet de création d'entreprise.",
                'tags' => ['Excel', 'Canva'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 9,
                'title' => 'Plateforme e-commerce pour produits artisanaux locaux',
                'description' => "Concevoir une plateforme e-commerce dédiée à la vente de produits artisanaux locaux, avec gestion des commandes, paiement en ligne et avis clients.",
                'tags' => ['Laravel', 'React', 'Stripe', 'PostgreSQL'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 8,
                'title' => 'Système de gestion des ressources humaines (SIRH léger)',
                'description' => 'Développer un système léger de gestion des ressources humaines couvrant les congés, les absences, les évaluations et les dossiers du personnel.',
                'tags' => ['Laravel', 'MySQL', 'Bootstrap'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 7,
                'title' => "Application de gestion de projets et de tâches d'équipe",
                'description' => "Concevoir une application collaborative de suivi de projets et de tâches d'équipe, avec attribution, échéances et tableaux de suivi.",
                'tags' => ['Vue.js', 'Laravel', 'GraphQL'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 7,
                'title' => "Outil d'analyse de la performance financière d'entreprises",
                'description' => "Développer un outil permettant d'analyser et de comparer la performance financière de plusieurs entreprises à partir de leurs états financiers.",
                'tags' => ['Power BI', 'Excel', 'SQL'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::L3, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 8,
                'title' => 'Plan stratégique de transformation digitale pour une PME',
                'description' => "Élaborer un plan stratégique de transformation digitale pour une PME, identifiant les processus à automatiser et les outils numériques à déployer.",
                'tags' => ['Power Apps', 'Power Automate', 'Excel'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 10,
                'title' => 'Système de prévision des ventes par analyse de données',
                'description' => 'Concevoir un modèle de prévision des ventes basé sur l\'analyse de données historiques et de facteurs saisonniers.',
                'tags' => ['Python', 'Pandas', 'Power BI'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 11,
                'title' => "Plateforme de gestion de la chaîne d'approvisionnement (ERP léger)",
                'description' => "Développer une plateforme légère de gestion de la chaîne d'approvisionnement couvrant les achats, les stocks et les fournisseurs.",
                'tags' => ['SAP', 'Laravel', 'PostgreSQL'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 10,
                'title' => "Outil d'aide à la décision pour l'investissement",
                'description' => "Concevoir un outil de modélisation financière permettant de comparer plusieurs scénarios d'investissement et d'évaluer leur rentabilité.",
                'tags' => ['Python', 'R', 'Excel'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Intermediate, 'weeks' => 9,
                'title' => 'Système de gestion de la performance et des KPI d\'entreprise',
                'description' => "Développer un système centralisant le suivi des indicateurs clés de performance d'une entreprise et automatisant la production des rapports.",
                'tags' => ['Power BI', 'Power Automate', 'SQL'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::M1, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 11,
                'title' => 'Modélisation de la fidélisation client par segmentation',
                'description' => 'Étudier et modéliser le comportement des clients afin de les segmenter et de proposer des actions de fidélisation ciblées.',
                'tags' => ['Python', 'Scikit-learn', 'Power BI'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 13,
                'title' => "Plateforme d'intelligence économique et de veille concurrentielle",
                'description' => "Concevoir une plateforme de collecte et d'analyse d'informations sur le marché et les concurrents afin d'orienter les décisions stratégiques.",
                'tags' => ['Python', 'NLP', 'Power BI'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 13,
                'title' => "Système de gestion intégrée des risques d'entreprise (ERM)",
                'description' => "Développer un système permettant d'identifier, d'évaluer et de suivre les risques majeurs auxquels une entreprise est exposée.",
                'tags' => ['Power BI', 'Excel', 'R'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 14,
                'title' => 'Modèle de valorisation d\'entreprise et simulation financière',
                'description' => 'Concevoir un modèle avancé de valorisation d\'entreprise intégrant plusieurs méthodes (DCF, multiples) et des simulations de scénarios.',
                'tags' => ['Excel VBA', 'Python', 'R'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 14,
                'title' => 'Plateforme de gestion de portefeuille de projets stratégiques (PPM)',
                'description' => 'Développer une plateforme de pilotage d\'un portefeuille de projets stratégiques, avec priorisation, suivi budgétaire et tableaux de bord exécutifs.',
                'tags' => ['MS Project', 'Power BI', 'Power Apps'],
            ],
            [
                'filiere' => 'MGT', 'level' => StudyLevel::M2, 'difficulty' => ProjectDifficulty::Advanced, 'weeks' => 14,
                'title' => 'Plan stratégique de transformation organisationnelle à 5 ans',
                'description' => 'Réaliser une étude prospective et élaborer un plan stratégique de transformation organisationnelle à horizon cinq ans pour une entreprise donnée.',
                'tags' => ['Excel', 'Power BI', 'R'],
            ],
        ];
    }
}

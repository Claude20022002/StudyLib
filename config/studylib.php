<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Navigation application (maquette Dashboard)
    |--------------------------------------------------------------------------
    */
    'nav' => [
        'main' => [
            ['label' => 'Accueil', 'route' => 'dashboard', 'icon' => 'home'],
            ['label' => 'Bibliothèque', 'route' => 'documents.index', 'icon' => 'library'],
            ['label' => 'Stages', 'route' => 'internship-reviews.index', 'icon' => 'briefcase'],
            ['label' => 'Projets', 'route' => 'project-ideas.index', 'icon' => 'layers'],
            // 'sauf' : rôles qui ne voient pas l'entrée (les événements étudiants ne concernent pas les enseignants)
            ['label' => 'Événements', 'route' => 'events.index', 'icon' => 'calendar', 'sauf' => ['teacher']],
        ],
        'personal' => [
            ['label' => 'Mes dépôts', 'route' => 'documents.index', 'icon' => 'upload', 'query' => ['mine' => '1']],
            ['label' => 'Favoris', 'route' => null, 'icon' => 'bookmark', 'disabled' => true],
            ['label' => 'Profil', 'route' => 'profile.show', 'icon' => 'user'],
        ],
        'mobile' => [
            ['label' => 'Accueil', 'route' => 'dashboard', 'icon' => 'home'],
            ['label' => 'Biblio', 'route' => 'documents.index', 'icon' => 'library'],
            ['label' => 'Stages', 'route' => 'internship-reviews.index', 'icon' => 'briefcase'],
            ['label' => 'Projets', 'route' => 'project-ideas.index', 'icon' => 'layers'],
            ['label' => 'Profil', 'route' => 'profile.show', 'icon' => 'user'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation administration (maquette Admin)
    |--------------------------------------------------------------------------
    */
    'nav_admin' => [
        ['label' => 'Tableau de bord', 'route' => 'admin.moderation.index', 'icon' => 'grid'],
        ['label' => 'Modération', 'route' => 'admin.moderation.index', 'icon' => 'shield'],
        ['label' => 'Événements', 'route' => 'admin.events.index', 'icon' => 'calendar'],
    ],

    'ai' => [
        // Durée de conservation des demandes et réponses de l'IA, en jours (0 : jamais effacées)
        'retention_days' => (int) env('AI_RETENTION_DAYS', 90),
    ],

    'document_filters' => [
        ['value' => 'all', 'label' => 'Tout'],
        ['value' => 'cours', 'label' => 'Cours'],
        ['value' => 'td', 'label' => 'TD'],
        ['value' => 'examen', 'label' => 'Examens'],
        ['value' => 'exercice', 'label' => 'Exercices'],
        ['value' => 'article', 'label' => 'Articles'],
    ],

];

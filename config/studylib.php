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
            ['label' => 'Événements', 'route' => 'events.index', 'icon' => 'calendar'],
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
        ['label' => 'Événements', 'route' => 'events.index', 'icon' => 'calendar'],
    ],

    'document_filters' => [
        ['value' => 'all', 'label' => 'Tout'],
        ['value' => 'cours', 'label' => 'Cours'],
        ['value' => 'td', 'label' => 'TD'],
        ['value' => 'examen', 'label' => 'Examens'],
    ],

];

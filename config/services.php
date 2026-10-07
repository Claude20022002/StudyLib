<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'claude' => [
        'key' => env('CLAUDE_API_KEY'),
        'url' => env('CLAUDE_API_URL', 'https://api.anthropic.com/v1/messages'),
        'model' => env('CLAUDE_MODEL', 'claude-3-5-sonnet-latest'),
    ],

    'youtube' => [
        'key' => env('YOUTUBE_API_KEY'),
    ],

    // Import des supports depuis Google Drive (studylib:import-drive) : clé JSON d'un compte de
    // service, hors Git ; le dossier à importer est partagé en Lecteur avec son adresse
    'google_drive' => [
        'credentials' => env('GOOGLE_DRIVE_CREDENTIALS') ?: storage_path('app/private/google-drive.json'),
        // Dossier importé quand la commande est lancée sans argument
        'folder' => env('GOOGLE_DRIVE_FOLDER_ID'),
        // Correspondance facultative nom de dossier → code de module (dossiers Classroom aux intitulés libres)
        'module_map' => env('GOOGLE_DRIVE_MODULE_MAP') ?: storage_path('app/private/drive-modules.json'),
        // Filière à laquelle rattacher les documents quand le Drive ne la précise pas (ex. IIIA)
        'filiere' => env('GOOGLE_DRIVE_FILIERE'),
    ],

];

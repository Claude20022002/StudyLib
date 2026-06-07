<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'StudyLib') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="sl-guest min-h-screen bg-surface antialiased">
    <x-ui.skip-link />

    <div class="sl-guest-page">
        <main id="main-content" class="sl-guest-page__main" tabindex="-1">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>

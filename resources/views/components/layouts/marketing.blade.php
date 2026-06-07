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
<body class="sl-marketing min-h-screen bg-card">
    <x-ui.skip-link />

    <main id="main-content" tabindex="-1">
        {{ $slot }}
    </main>

    <x-ui.flash-messages />

    @livewireScripts
</body>
</html>

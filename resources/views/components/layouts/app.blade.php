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
<body class="min-h-screen">
    <div class="sl-app">
        <livewire:layout.app-sidebar />

        <div class="sl-main">
            <livewire:layout.app-topbar :title="$header ?? null" />

            <main id="main-content" class="sl-content" tabindex="-1">
                {{ $slot }}
            </main>
        </div>
    </div>

    <livewire:layout.app-bottom-nav />

    @livewireScripts
</body>
</html>

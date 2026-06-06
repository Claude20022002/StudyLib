<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Administration — '.config('app.name', 'StudyLib') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-surface text-sm">
    <div class="sl-admin-app">
        <livewire:layout.admin-sidebar />

        <div class="sl-main">
            <header class="sl-admin-topbar">
                @isset($header)
                    <div>
                        <h1 class="text-h3 font-bold tracking-tight">{{ $header }}</h1>
                        @isset($breadcrumb)
                            <p class="text-xs text-muted">{{ $breadcrumb }}</p>
                        @endisset
                    </div>
                @endisset
                <div class="flex-1"></div>
                {{ $topbar ?? '' }}
            </header>

            <main id="main-content" class="sl-admin-content" tabindex="-1">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>

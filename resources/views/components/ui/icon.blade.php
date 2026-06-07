@props([
    'name',
    'class' => '',
])

@php
    $classes = trim('sl-ico '.$class);
@endphp

@switch($name)
    @case('home')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        @break
    @case('library')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        @break
    @case('briefcase')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M20 7h-3V5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM9 5h6v2H9z"/></svg>
        @break
    @case('layers')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M12 2 2 7l10 5 10-5z"/><path d="m2 17 10 5 10-5M2 12l10 5 10-5"/></svg>
        @break
    @case('calendar')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        @break
    @case('upload')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
        @break
    @case('bookmark')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
        @break
    @case('user')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 12 0v1"/></svg>
        @break
    @case('search')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        @break
    @case('bell')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 0 1-3.4 0"/></svg>
        @break
    @case('plus')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M12 5v14M5 12h14"/></svg>
        @break
    @case('shield')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        @break
    @case('grid')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        @break
    @case('chevron-left')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="m15 18-6-6 6-6"/></svg>
        @break
    @case('chevron-right')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="m9 18 6-6-6-6"/></svg>
        @break
    @case('alert')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2.2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
        @break
    @case('info')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><circle cx="12" cy="12" r="9"/><path d="M12 10v6M12 7h.01"/></svg>
        @break
    @case('x')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2.4', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M18 6 6 18M6 6l12 12"/></svg>
        @break
    @case('mail')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
        @break
    @case('lock')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
        @break
    @case('eye')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
        @break
    @case('eye-off')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-10-7-10-7a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
        @break
    @case('shield-check')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
        @break
    @case('file')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
        @break
    @case('check')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        @break
    @case('trend-up')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2.5', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M7 17 17 7M9 7h8v8"/></svg>
        @break
    @case('download')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
        @break
    @case('star')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M12 2.5l2.9 5.9 6.5.95-4.7 4.58 1.1 6.47L12 17.4l-5.8 3.05 1.1-6.47L2.6 9.35l6.5-.95z"/></svg>
        @break
    @case('message')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        @break
    @case('clock')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2.4', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        @break
    @case('sparkles')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2.2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M12 3v2M12 19v2M5 12H3M21 12h-2M6.3 6.3 4.9 4.9M19.1 19.1l-1.4-1.4M17.7 6.3l1.4-1.4M4.9 19.1l1.4-1.4"/><circle cx="12" cy="12" r="3.5"/></svg>
        @break
    @case('arrow-right')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        @break
    @case('map-pin')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        @break
    @case('filter')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'aria-hidden' => 'true']) }}><path d="M4 6h16M7 12h10M10 18h4"/></svg>
        @break
    @case('wave')
        <svg {{ $attributes->merge(['class' => $classes, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}><path d="M7 11V7a2 2 0 0 1 4 0v4"/><path d="M11 11V5a2 2 0 0 1 4 0v6"/><path d="M15 11V6a2 2 0 0 1 4 0v5c0 5-4 8-8 8-3.5 0-7-2-7-6 0-2 0-4 2-4a2 2 0 0 1 2 2v1"/></svg>
        @break
    @default
        <span {{ $attributes->merge(['class' => $classes, 'aria-hidden' => 'true']) }}></span>
@endswitch

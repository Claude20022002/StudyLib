@php
    $attr = config('flaticon.attribution', []);
@endphp

<p class="sl-flaticon-attrib">
    Icônes par
    <a href="{{ $attr['url'] ?? 'https://www.flaticon.com/' }}" target="_blank" rel="noopener noreferrer">{{ $attr['label'] ?? 'Flaticon' }}</a>
</p>

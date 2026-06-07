@php
    $pageTitle = $document->title.' · '.config('app.name');
@endphp

<x-layouts.app :title="$pageTitle" :breadcrumb="$breadcrumb">
    <livewire:documents.show :document="$document" />
</x-layouts.app>

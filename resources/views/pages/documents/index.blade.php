<x-layouts.app :title="($pageTitle ?? 'Bibliothèque').' · '.config('app.name')" :header="$pageTitle ?? 'Bibliothèque'">
    <livewire:documents.index />
</x-layouts.app>

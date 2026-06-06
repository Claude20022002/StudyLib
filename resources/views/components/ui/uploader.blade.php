@props([
    'title' => 'Glissez-déposez votre fichier ici',
    'hint' => 'ou parcourez vos fichiers · PDF, DOCX, PPTX · 50 Mo max',
    'name' => 'file',
    'accept' => '.pdf,.doc,.docx,.ppt,.pptx',
])

<div {{ $attributes->merge(['class' => 'sl-uploader']) }}>
    <input
        type="file"
        name="{{ $name }}"
        accept="{{ $accept }}"
        class="sr-only"
        id="{{ $name }}-input"
    />
    <label for="{{ $name }}-input" class="block cursor-pointer">
        <div class="sl-uploader-ico">
            <x-ui.icon name="upload" class="h-6 w-6" />
        </div>
        <div class="sl-uploader-title">{{ $title }}</div>
        <div class="sl-uploader-sub">{{ $hint }}</div>
    </label>
    {{ $slot }}
</div>

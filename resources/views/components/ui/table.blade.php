<div {{ $attributes->merge(['class' => 'sl-table-wrap']) }}>
    <table class="sl-table">
        @if (isset($head))
            <thead>
                {{ $head }}
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>

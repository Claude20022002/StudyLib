@php
    $messages = collect([
        'success' => session('success'),
        'warning' => session('warning'),
        'danger' => session('error') ?? session('danger'),
        'info' => session('info'),
        'status' => session('status'),
    ])->filter();
@endphp

@if ($messages->isNotEmpty())
    <div class="sl-toast-stack" aria-live="polite">
        @foreach ($messages as $variant => $text)
            <x-ui.toast
                :variant="$variant === 'status' ? 'success' : $variant"
                :title="is_string($text) ? $text : ''"
                dismissible
            />
        @endforeach
    </div>
@endif

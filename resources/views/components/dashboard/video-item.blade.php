@props([
    'video',
])

<a href="https://www.youtube.com/watch?v={{ $video->video_id }}" target="_blank" rel="noopener noreferrer" class="sl-video">
    <div class="sl-video-thumb">
        @if ($video->thumbnail_url)
            <img src="{{ $video->thumbnail_url }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="lazy" />
        @endif
        <span class="sl-video-play" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="currentColor" class="ml-px h-3 w-3 text-[#E3402F]"><path d="M8 5v14l11-7z"/></svg>
        </span>
        @if ($video->duration)
            <span class="sl-video-dur">{{ gmdate('i:s', $video->duration) }}</span>
        @endif
    </div>
    <div class="min-w-0">
        <h4 class="text-sm leading-snug font-semibold text-pretty">{{ $video->title }}</h4>
        @if ($video->channel)
            <p class="mt-1 text-xs text-muted">{{ $video->channel }}</p>
        @endif
    </div>
</a>

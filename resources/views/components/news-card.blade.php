@props([
    'news' => null,
    'showFullDescription' => false,
    'showAttachments' => true,
    'showReadMore' => true,
    'showMeta' => true,
    'cardClass' => 'news-card',
    'imageClass' => '',
])

@php
    $images = $news->attachments->filter(function ($att) {
        return str_starts_with($att->type, 'image/');
    });
    $files = $news->attachments->filter(function ($att) {
        return !str_starts_with($att->type, 'image/');
    });
    $thumbnail = $images->first();
    $hasImage = $thumbnail !== null;
@endphp

<div class="{{ $cardClass }}">
    <div class="news-card-header">
        <h3 class="news-card-title">{{ $news->title }}</h3>
        <span class="news-card-date">{{ new DateTime($news->created_at)->format('d.m.Y') }}</span>
    </div>

    <p class="news-card-desc">
         {!! $news->description !!}
    </p>

    <div class="news-card-attachments">
            @if ($showAttachments && $news->attachments->count() > 0)
                <div class="attachments-container">
                    <div class="attachments-header">Вложения: {{ $news->attachments->count() }} 
                    </div>
                    
                    <div class="attachments-list">
                        @foreach ($news->attachments as $attachment)
                            @php
                                $icon = match(true) {
                                    str_starts_with($attachment->type, 'image/') => 'fa-image',
                                    str_starts_with($attachment->type, 'application/pdf') => 'fa-file-pdf',
                                    str_starts_with($attachment->type, 'application/msword') => 'fa-file-word',
                                    str_starts_with($attachment->type, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') => 'fa-file-word',
                                    str_starts_with($attachment->type, 'application/vnd.ms-excel') => 'fa-file-excel',
                                    str_starts_with($attachment->type, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') => 'fa-file-excel',
                                    str_starts_with($attachment->type, 'application/zip') => 'fa-file-archive',
                                    default => 'fa-file'
                                };
                            @endphp
                            
                            <a href="{{ route('news.download', ['news' => $news->id, 'attachment' => $attachment->id]) }}" 
                               class="attachment-item"
                               download>
                                <i class="fas {{ $icon }}"></i>
                                <span class="attachment-name">{{ $attachment->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
    </div>
</div>
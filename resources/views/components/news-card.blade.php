@props([
    'news' => null,
    'showFullDescription' => false,
    'showAttachments' => true,
    'showReadMore' => true,
    'showMeta' => true,
    'thumbnailPosition' => 'top', 
    'cardClass' => '',
    'imageClass' => '',
])

@php
    $images = $news->attachments->filter(function($att) {
        return str_starts_with($att->type, 'image/');
    });
    $files = $news->attachments->filter(function($att) {
        return !str_starts_with($att->type, 'image/');
    });
    $thumbnail = $images->first();
    $hasImage = $thumbnail !== null;
@endphp

<div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden {{ $cardClass }}">
    @if($thumbnailPosition === 'top' && $hasImage)
        <div class="w-full h-48 overflow-hidden {{ $imageClass }}">
            <img src="{{ Storage::url($thumbnail->path) }}" 
                 alt="{{ $news->title }}"
                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
        </div>
    @endif

    <div class="p-6">
        <div class="flex {{ $thumbnailPosition === 'left' || $thumbnailPosition === 'right' ? 'flex-row gap-4' : 'flex-col' }}">
            @if(($thumbnailPosition === 'left' || $thumbnailPosition === 'right') && $hasImage)
                <div class="flex-shrink-0 w-32 h-32 rounded-lg overflow-hidden {{ $imageClass }}">
                    <img src="{{ Storage::url($thumbnail->path) }}" 
                         alt="{{ $news->title }}"
                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <!-- Заголовок -->
                <a href="" class="block group">
                    <h2 class="text-xl font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                        {{ $news->title }}
                    </h2>
                </a>

                <!-- Мета-информация -->
                @if($showMeta)
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500 mt-2">
                        <span class="flex items-center">
                            <i class="far fa-calendar-alt mr-1.5"></i>
                            {{ $news->created_at }}
                        </span>
                        
                        @if($showAttachments && $news->attachments->count() > 0)
                            <span class="flex items-center">
                                <i class="far fa-paperclip mr-1.5"></i>
                                {{ $news->attachments->count() }} вложений
                            </span>
                        @endif

                        @if($images->count() > 0)
                            <span class="flex items-center">
                                <i class="far fa-image mr-1.5"></i>
                                {{ $images->count() }} изображений
                            </span>
                        @endif
                    </div>
                @endif

                <!-- Описание -->
                <div class="mt-3 text-gray-700 leading-relaxed">
                    @if($showFullDescription)
                        <div class="whitespace-pre-wrap">
                            {{ $news->description }}
                        </div>
                    @else
                        <p class="line-clamp-3">
                            {{ Str::limit($news->description, 200) }}
                        </p>
                    @endif
                </div>

                <!-- Миниатюры изображений (сетка) -->
                @if($showAttachments && $images->count() > 1 && !$showFullDescription)
                    <div class="mt-4 grid grid-cols-3 gap-2">
                        @foreach($images->take(3) as $image)
                            <div class="aspect-square rounded-lg overflow-hidden bg-gray-100">
                                <img src="{{ Storage::url($image->path) }}" 
                                     alt="{{ $image->name }}"
                                     class="w-full h-full object-cover">
                            </div>
                        @endforeach
                        @if($images->count() > 3)
                            <div class="aspect-square rounded-lg bg-blue-100 flex items-center justify-center text-sm font-medium text-blue-600">
                                +{{ $images->count() - 3 }}
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Футер карточки -->
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <!-- Теги вложений -->
                    @if($showAttachments && $news->attachments->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($news->attachments->take(3) as $attachment)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                    <i class="fas {{ 
                                        str_starts_with($attachment->type, 'image/') ? 'fa-image text-blue-500' : 
                                        (str_starts_with($attachment->type, 'application/pdf') ? 'fa-file-pdf text-red-500' :
                                        (str_starts_with($attachment->type, 'application/msword') ? 'fa-file-word text-blue-700' :
                                        (str_starts_with($attachment->type, 'application/vnd.ms-excel') ? 'fa-file-excel text-green-700' :
                                        'fa-file text-gray-500'))) 
                                    }} mr-1 text-xs"></i>
                                    {{ Str::limit($attachment->name, 15) }}
                                </span>
                            @endforeach
                            @if($news->attachments->count() > 3)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                    +{{ $news->attachments->count() - 3 }}
                                </span>
                            @endif
                        </div>
                    @endif

                    <!-- Кнопка "Читать далее" -->
                    @if($showReadMore && !$showFullDescription)
                        <a href="" 
                           class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors group">
                            Читать далее
                            <i class="fas fa-arrow-right ml-1.5 text-xs group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@props(['type' => null, 'size' => 20])

@php
    /**
     * Каждая иконка — набор SVG-элементов.
     * Стрелки/линии — stroke, акценты (заливки) — fill с opacity.
     * Всё рисуется в сетке 24×24.
     */
    $icons = [

        // 💬 Комментарий — диалоговое облако с хвостиком и тремя точками
        'issue_comment' => '
            <path d="M21 14.5a2.5 2.5 0 0 1-2.5 2.5H8l-4.5 4V5.5A2.5 2.5 0 0 1 6 3h12.5A2.5 2.5 0 0 1 21 5.5z"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.7"
                  stroke-linecap="round"
                  stroke-linejoin="round"/>
            <circle cx="8.5"  cy="11" r="1" fill="currentColor"/>
            <circle cx="12"   cy="11" r="1" fill="currentColor"/>
            <circle cx="15.5" cy="11" r="1" fill="currentColor"/>
        ',

        // 🔄 Смена статуса — круг со стрелкой + точка на «полюсе»
        'issue_status_changed' => '
            <path d="M20.5 12a8.5 8.5 0 1 1-2.5-6"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.7"
                  stroke-linecap="round"
                  stroke-linejoin="round"/>
            <path d="M21 4.5v5h-5"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.7"
                  stroke-linecap="round"
                  stroke-linejoin="round"/>
            <circle cx="12" cy="12" r="2" fill="currentColor" opacity="0.55"/>
        ',

        // 📰 Новость — газета с заголовком и строками
        'news_created' => '
            <path d="M4 21.5h15a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-9l-2-1.5H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2z"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.7"
                  stroke-linecap="round"
                  stroke-linejoin="round"/>
            <rect x="10" y="6" width="8" height="4.5" rx="0.6"
                  fill="currentColor" opacity="0.55"/>
            <path d="M10 14h8M10 17h5"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.7"
                  stroke-linecap="round"/>
        ',
    ];

    // Колокольчик — fallback на случай нового типа уведомления
    $fallback = '
        <path d="M18 8.5a6 6 0 0 0-12 0c0 6.5-2.5 8.5-2.5 8.5h17S18 15 18 8.5z"
              fill="none"
              stroke="currentColor"
              stroke-width="1.7"
              stroke-linecap="round"
              stroke-linejoin="round"/>
        <path d="M13.7 20.5a2 2 0 0 1-3.4 0"
              fill="none"
              stroke="currentColor"
              stroke-width="1.7"
              stroke-linecap="round"/>
        <circle cx="12" cy="8.5" r="2" fill="currentColor" opacity="0.55"/>
    ';

    $svgBody = $icons[$type] ?? $fallback;
@endphp

<svg xmlns="http://www.w3.org/2000/svg"
     width="{{ $size }}"
     height="{{ $size }}"
     viewBox="0 0 24 24"
     class="notif-icon notif-icon--{{ $type ?? 'default' }}"
     aria-hidden="true"
     focusable="false">
    {!! $svgBody !!}
</svg>
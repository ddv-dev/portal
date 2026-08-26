@if ($paginator->hasPages())
    <nav class="pagination-wrapper" role="navigation" aria-label="Пагинация">
        <div class="pagination-mobile">
            {{-- Мобильная версия --}}
            <div class="pagination-mobile-buttons">
                @if ($paginator->onFirstPage())
                    <span class="pagination-mobile-button disabled">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Назад
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="pagination-mobile-button">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Назад
                    </a>
                @endif

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="pagination-mobile-button">
                        Вперед
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                @else
                    <span class="pagination-mobile-button disabled">
                        Вперед
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                @endif
            </div>
        </div>

        {{-- Десктопная версия --}}
        <div class="pagination-desktop">
            <div class="pagination-info">
                <span class="pagination-info-text">
                    Показано 
                    <span class="pagination-info-number">{{ $paginator->firstItem() }}</span>
                    — 
                    <span class="pagination-info-number">{{ $paginator->lastItem() }}</span>
                    из 
                    <span class="pagination-info-number">{{ $paginator->total() }}</span>
                    записей
                </span>
            </div>

            <ul class="pagination-list">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="pagination-item disabled">
                        <span class="pagination-link">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </li>
                @else
                    <li class="pagination-item">
                        <a href="{{ $paginator->previousPageUrl() }}" class="pagination-link" rel="prev">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="pagination-item disabled">
                            <span class="pagination-link dots">{{ $element }}</span>
                        </li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="pagination-item active">
                                    <span class="pagination-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="pagination-item">
                                    <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="pagination-item">
                        <a href="{{ $paginator->nextPageUrl() }}" class="pagination-link" rel="next">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </li>
                @else
                    <li class="pagination-item disabled">
                        <span class="pagination-link">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </li>
                @endif
            </ul>

            {{-- Выбор количества записей на странице --}}
            <div class="pagination-per-page">
                <label for="perPage" class="pagination-per-page-label">
                    На странице:
                </label>
                <select id="perPage" class="pagination-per-page-select" onchange="window.location.href = this.value">
                    @foreach([10, 25, 50, 100] as $option)
                        <option value="{{ request()->fullUrlWithQuery(['perPage' => $option]) }}" 
                                {{ $paginator->perPage() == $option ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </nav>
@endif

<style>
    /* ============================================
   Базовые стили пагинации
   ============================================ */

.pagination-wrapper {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-top: 30px;
    padding: 20px 0;
    border-top: 1px solid #e5e7eb;
}

/* --------------------------------------------
   Мобильная версия
   -------------------------------------------- */

.pagination-mobile {
    display: none;
}

.pagination-mobile-buttons {
    display: flex;
    justify-content: space-between;
    gap: 12px;
}

.pagination-mobile-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    color: #374151;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
}

.pagination-mobile-button:hover:not(.disabled) {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.pagination-mobile-button.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.pagination-mobile-button svg {
    flex-shrink: 0;
}

/* --------------------------------------------
   Десктопная версия
   -------------------------------------------- */

.pagination-desktop {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

/* Информация о количестве записей */
.pagination-info {
    flex-shrink: 0;
}

.pagination-info-text {
    color: #6b7280;
    font-size: 14px;
    line-height: 1.5;
}

.pagination-info-number {
    font-weight: 600;
    color: #111827;
}

/* Список страниц */
.pagination-list {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 4px;
}

.pagination-item {
    display: inline-block;
}

.pagination-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 10px;
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    color: #374151;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
    user-select: none;
}

.pagination-link:hover:not(.active):not(.dots) {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.pagination-item.active .pagination-link {
    background: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
    cursor: default;
}

.pagination-item.disabled .pagination-link {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.pagination-link.dots {
    border: none;
    background: transparent;
    color: #6b7280;
    cursor: default;
    padding: 0 4px;
    min-width: 24px;
}

.pagination-link svg {
    flex-shrink: 0;
}

/* Выбор количества записей */
.pagination-per-page {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.pagination-per-page-label {
    color: #6b7280;
    font-size: 14px;
    white-space: nowrap;
}

.pagination-per-page-select {
    padding: 6px 28px 6px 12px;
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    color: #374151;
    font-size: 14px;
    font-weight: 500;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236b7280' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pagination-per-page-select:hover {
    border-color: #9ca3af;
}

.pagination-per-page-select:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* --------------------------------------------
   Адаптивность
   -------------------------------------------- */

@media (max-width: 768px) {
    .pagination-desktop {
        display: none;
    }
    
    .pagination-mobile {
        display: block;
    }
}

@media (max-width: 480px) {
    .pagination-wrapper {
        padding: 16px 0;
        margin-top: 20px;
    }
    
    .pagination-mobile-button {
        padding: 8px 12px;
        font-size: 13px;
        border-radius: 6px;
    }
}
</style>
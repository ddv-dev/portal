@extends('layouts.app')
@section('title', 'Стандартные операционные процедуры')

@section('content')
    <div class="content-wrapper">
        <!-- Левое меню -->
        <aside class="sidebar-1">
            <!-- Поиск -->
            <div class="search-box">
                <span class="section-title-2">Поиск</span>
                <form method="GET" action="{{ route('sop') }}" id="searchForm">
                    <div class="search-input-group">
                        <input type="text" name="search" class="search-input" placeholder="Поиск по названию..."
                            value="{{ request()->input('search') }}" id="searchInput">
                        <button class="button button--secondary" type="submit">
                            Найти
                        </button>
                        @if(request()->input('search') || request()->input('year'))
                            <div class="reset-wrapper">
                                <a href="{{ route('sop') }}" class="reset-btn" data-reset-all>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18" />
                                        <line x1="6" y1="6" x2="18" y2="18" />
                                    </svg>
                                    Сбросить все фильтры
                                </a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <div class="column-group">
                <span class="section-title-2">Меню</span>

                <!-- Года -->
                @if($years->count() > 0)
                    <div class="filter-section">
                        <h6 class="filter-title">По годам</h6>
                        <ul class="filter-menu">
                            <li>
                                <a href="#" data-reset="year"
                                    class="filter-link {{ !request()->input('year') ? 'active' : '' }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <rect x="2" y="2" width="20" height="20" rx="2" />
                                        <path d="M2 8h20" />
                                    </svg>
                                    Все годы
                                </a>
                            </li>
                            @foreach($years as $year)
                                <li>
                                    <a href="#" data-filter="year" data-value="{{ $year }}"
                                        class="filter-link {{ request()->input('year') == $year ? 'active' : '' }}">

                                        {{ $year }} год
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

        </aside>

        <!-- Основной контент -->
        <main class="main-content-1">
            <!-- Активные фильтры -->
            @if(request()->input('search') || request()->input('year'))
                <div class="active-filters">
                    <span class="filter-label">Активные фильтры:</span>

                    @if(request()->input('search'))
                        <span class="filter-tag filter-tag-search">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <path d="M21 21l-4.35-4.35" />
                            </svg>
                            {{ request()->input('search') }}
                        </span>
                    @endif
                    @if(request()->input('year'))
                        <span class="filter-tag filter-tag-corpus">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="2" width="20" height="20" rx="2" />
                            </svg>
                            {{ request()->input('year') }} год
                        </span>
                    @endif
                </div>
            @endif

            <!-- Таблица приказов -->
            @if($sops->count() > 0)
                <div class="table-wrapper-1">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="col-primary">Наименование документа</th>
                                    <th class="col-tertiary">Дата разработки</th>
                                    <th class="col-tertiary">Версия</th>
                                    <th class="col-tertiary">Вложения</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sops as $sop)
                                    <tr>
                                        <td class="col-order-number">
                                            <div class="cell-name">
                                                <strong>{{ $sop->title ?: '—' }}</strong>
                                            </div>
                                        </td>
                                        <td class="col-order-date">
                                            @if($sop->created_at)
                                                <span class="cell-location">
                                                    {{ $sop->created_at->format('d.m.Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="col-order-status">
                                            @if($sop->version)
                                                <strong>{{ $sop->version ?: '—' }}</strong>
                                            @else
                                                <strong>1</strong>
                                            @endif
                                        </td>
                                        <td class="col-order-attachments">
                                            @if($sop->attachments->count() > 0)
                                                <span class="attachment-count">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2">
                                                        <path
                                                            d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48" />
                                                    </svg>
                                                    {{ $sop->attachments->count() }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Пагинация -->
                <div class="pagination-wrapper">
                    {{ $sops->appends(request()->query())->links() }}
                </div>
            @else
                <div class="empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="M21 21l-4.35-4.35" />
                    </svg>
                    <h4>Приказы не найдены</h4>
                    <p>Попробуйте изменить параметры поиска</p>
                </div>
            @endif
        </main>
    </div>

@endsection
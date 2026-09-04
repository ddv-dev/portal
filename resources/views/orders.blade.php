{{-- resources/views/orders.blade.php --}}
@extends('layouts.app')

@section('title', 'Приказы')

@section('content')
    <div class="content-wrapper">
        <!-- Левое меню -->
        <aside class="sidebar-1">
            <!-- Поиск -->
            <div class="search-box">
                <span class="section-title-2">Поиск</span>
                <form method="GET" action="{{ route('orders') }}" id="searchForm">
                    <div class="search-input-group">
                        <input type="text" name="search" class="search-input"
                            placeholder="Поиск по номеру, названию..." value="{{ request()->input('search') }}"
                            id="searchInput">
                        <button class="button button--secondary" type="submit">
                            Найти
                        </button>
                        @if(request()->input('search') || request()->input('status') || request()->input('year'))
                            <div class="reset-wrapper">
                                <a href="{{ route('orders') }}" class="reset-btn" data-reset-all>
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

            <!-- Меню -->
            <div class="column-group">
                <span class="section-title-2">Меню</span>

                <!-- Статус -->
                <div class="filter-section">
                    <h6 class="filter-title">Статус</h6>
                    <ul class="filter-menu">
                        <li>
                            <a href="#" data-filter="status" data-value="all"
                                class="filter-link {{ !request()->input('status') || request()->input('status') === 'all' ? 'active' : '' }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="2" y="2" width="20" height="20" rx="2" />
                                    <path d="M2 8h20" />
                                    <path d="M8 2v20" />
                                </svg>
                                Все приказы
                            </a>
                        </li>
                        <li>
                            <a href="#" data-filter="status" data-value="active"
                                class="filter-link {{ request()->input('status') === 'active' ? 'active' : '' }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M12 8v8" />
                                    <path d="M8 12h8" />
                                </svg>
                                Действующие
                            </a>
                        </li>
                        <li>
                            <a href="#" data-filter="status" data-value="inactive"
                                class="filter-link {{ request()->input('status') === 'inactive' ? 'active' : '' }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M8 12h8" />
                                </svg>
                                Недействующие
                            </a>
                        </li>
                    </ul>
                </div>

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
            @if(request()->input('search') || request()->input('status') || request()->input('year'))
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
                    
                    @if(request()->input('status'))
                        <span class="filter-tag filter-tag-dept">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                            </svg>
                            {{ request()->input('status') === 'active' ? 'Действующие' : 'Недействующие' }}
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
            @if($orders->count() > 0)
                <div class="table-wrapper-1">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="col-order-number">Номер</th>
                                    <th class="col-order-title">Название</th>
                                    <th class="col-order-date">Дата</th>
                                    <th class="col-order-status">Статус</th>
                                    <th class="col-order-attachments">Вложения</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td class="col-order-number">
                                            <div class="cell-name">
                                                <strong>{{ $order->number ?: '—' }}</strong>
                                            </div>
                                        </td>
                                        <td class="col-order-title">
                                            <div class="cell-name">
                                                <strong>{{ $order->title }}</strong>
                                                @if($order->description)
                                                    <span class="cell-description">— {{ Str::limit($order->description, 50) }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="col-order-date">
                                            @if($order->created_at)
                                                <span class="cell-location">
                                                    {{ $order->created_at->format('d.m.Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="col-order-status">
                                            @if($order->is_active)
                                                <span class="status-badge status-active">Действующий</span>
                                            @else
                                                <span class="status-badge status-inactive">Недействующий</span>
                                            @endif
                                        </td>
                                        <td class="col-order-attachments">
                                            @if($order->attachments->count() > 0)
                                                <span class="attachment-count">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48" />
                                                    </svg>
                                                    {{ $order->attachments->count() }}
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
                    {{ $orders->appends(request()->query())->links() }}
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
{{-- resources/views/directory.blade.php --}}
@extends('layouts.app')

@section('title', 'Телефонный справочник')

@section('content')
    <div class="phone-book-wrapper">
        <!-- Левое меню -->
        <aside class="phone-book-sidebar">
            <!-- Поиск -->
            <div class="search-box">
                <form method="GET" action="{{ route('directory') }}" id="searchForm">
                    <div class="search-input-group">
                        <input type="text" name="search" class="search-input"
                            placeholder="Поиск по ФИО, отделу, телефону..." value="{{ request()->input('search') }}"
                            id="searchInput">
                        <button class="search-btn" type="submit">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <path d="M21 21l-4.35-4.35" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
            <!-- Сброс фильтров -->
            @if(request()->input('search') || request()->input('dept_id') || request()->input('corpus_id'))
                <div class="reset-wrapper">
                    <a href="{{ route('directory') }}" class="reset-btn" data-reset-all>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                        Сбросить все фильтры
                    </a>
                </div>
            @endif
            <!-- Местоположение -->
            <div class="filter-section">
                <h6 class="filter-title">Местоположение</h6>
                <ul class="filter-menu">
                    <li>
                        <a href="" data-reset="corpus_id"
                            class="filter-link {{ !request()->input('corpus_id') ? 'active' : '' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="2" y="2" width="20" height="20" rx="2" />
                                <path d="M2 8h20" />
                                <path d="M8 2v20" />
                            </svg>
                            Все здания
                        </a>
                    </li>
                    @if($buildings && $buildings->count() > 0)
                        @foreach($buildings as $building)
                            @if($building instanceof \App\Models\Building)
                                <li>
                                    <a href="#building-{{ $building->id }}" class="filter-link collapsed" data-bs-toggle="collapse"
                                        role="button">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                                    </svg>
                                        
                                        {{ $building->name }}
                                        <span class="arrow">▼</span>
                                    </a>
                                    <div class="sub-menu collapse" id="building-{{ $building->id }}">
                                        @if($building->corpuses && $building->corpuses->count() > 0)
                                            @foreach($building->corpuses as $corpus)
                                                @if($corpus instanceof \App\Models\Corpus)
                                                    <a href="#" data-filter="corpus_id" data-value="{{ $corpus->id }}"
                                                        class="filter-link sub-link {{ request()->input('corpus_id') == $corpus->id ? 'active' : '' }}">

                                                        {{ $corpus->name }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        @else
                                            <span class="empty-message">Нет корпусов</span>
                                        @endif
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    @else
                        <li><span class="empty-message">Нет зданий</span></li>
                    @endif
                </ul>
            </div>

            <!-- Отделения -->
            <div class="filter-section">
                <h6 class="filter-title">Отделения</h6>
                <ul class="filter-menu">
                    <li>
                        <a href="" data-reset="dept_id"
                            class="filter-link {{ !request()->input('dept_id') ? 'active' : '' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="2" y="2" width="20" height="20" rx="2" />
                                <path d="M2 8h20" />
                                <path d="M8 2v20" />
                            </svg>
                            Все отделы
                        </a>
                    </li>
                    @if($departments && $departments->count() > 0)
                        @foreach($departments as $typeName => $depts)
                            <li>
                                <a href="#dept-type-{{ Str::slug($typeName) }}" class="filter-link collapsed"
                                    data-bs-toggle="collapse" role="button">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" />
                                    </svg>
                                    {{ $typeName ?: 'Без типа' }}
                                    <span class="arrow">▼</span>
                                </a>
                                <div class="sub-menu collapse" id="dept-type-{{ Str::slug($typeName) }}">
                                    @foreach($depts as $dept)
                                        @if($dept instanceof \App\Models\Dept)
                                            <a href="#" data-filter="dept_id" data-value="{{ $dept->id }}"
                                                class="filter-link sub-link {{ request()->input('dept_id') == $dept->id ? 'active' : '' }}">
                                                <!--
                                                                    {{ $dept->name_short ?: $dept->name_full }}
                                                                    -->
                                                {{ $dept->name_full }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </li>
                        @endforeach
                    @else
                        <li><span class="empty-message">Нет отделов</span></li>
                    @endif
                </ul>
            </div>


        </aside>

        <!-- Основной контент -->
        <main class="phone-book-content">
            <!-- Заголовок -->
            <div class="content-header">
                <h2>Телефонный справочник</h2>
                <span class="count-badge">{{ $workers->total() }} сотрудников</span>
            </div>

            <!-- Активные фильтры -->
            @if(request()->input('search') || request()->input('dept_id') || request()->input('corpus_id'))
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
                    @if(request()->input('dept_id'))
                        @php
                            $dept = $departments ? $departments->flatten()->firstWhere('id', (int) request()->input('dept_id')) : null;
                        @endphp
                        @if($dept)
                            <span class="filter-tag filter-tag-dept">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="2" width="20" height="20" rx="2" />
                                    <path d="M2 8h20" />
                                </svg>
                                {{ $dept->name_short ?: $dept->name_full }}
                            </span>
                        @endif
                    @endif
                    @if(request()->input('corpus_id'))
                        @php
                            $corpus = $corpuses ? $corpuses->firstWhere('id', (int) request()->input('corpus_id')) : null;
                        @endphp
                        @if($corpus)
                            <span class="filter-tag filter-tag-corpus">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" />
                                    <path d="M3 9h18" />
                                </svg>
                                {{ $corpus->name }}
                            </span>
                        @endif
                    @endif
                </div>
            @endif

            <!-- Список сотрудников в табличном виде -->
            @if($workers->count() > 0)
                    @php
                        // Группируем сотрудников по отделам
                        $groupedWorkers = $workers->groupBy(function ($worker) {
                            $dept = $worker->deptWorkers->first()?->dept;
                            return $dept ? $dept->id : 'no_dept';
                        });
                    @endphp

                    @foreach($groupedWorkers as $deptId => $deptWorkers)
                        @php
                            $dept = $deptWorkers->first()->deptWorkers->first()?->dept;
                            $deptName = $dept ? $dept->name_full : 'Без отдела';
                            $deptShort = $dept ? $dept->name_short : 'Без отдела';
                        @endphp

                        <div class="dept-table-wrapper">
                            <div class="dept-table-header">
                                <h3>{{ $deptName }}</h3>
                                <span class="dept-count">{{ $deptWorkers->count() }} сотрудников</span>
                            </div>

                            <div class="table-responsive">
                                <table class="worker-table">
                                    <thead>
                                        <tr>
                                            <th class="col-worker">ФИО, должность</th>
                                            <th class="col-location">Расположение</th>
                                            <th class="col-phone">Местный</th>
                                            <th class="col-phone">Городской</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($deptWorkers as $worker)
                                            @php
                                                $deptWorker = $worker->deptWorkers->first();
                                                $position = $deptWorker?->position;

                                                // Собираем локацию
                                                $corpusDept = $deptWorker?->corpusDept;
                                                $corpus = $corpusDept?->corpus;
                                                $building = $corpus?->building;

                                                $locationParts = [];
                                                /*    if ($building)
                                                        $locationParts[] = $building->name;*/
                                                if ($corpus)
                                                    $locationParts[] = $corpus->name;
                                                if ($corpusDept && $corpusDept->floor)
                                                    $locationParts[] = $corpusDept->floor . ' этаж';
                                                if ($corpusDept && $corpusDept->sector)
                                                    $locationParts[] = 'сектор ' . $corpusDept->sector;
                                                $location = implode(' → ', $locationParts);

                                                // Разделяем телефоны на местные и городские
                                                $localPhones = [];
                                                $cityPhones = [];
                                                foreach ($worker->phones as $phone) {
                                                    if ($phone->is_city_number) {
                                                        $cityPhones[] = $phone->phone_number;
                                                    } else {
                                                        $localPhones[] = $phone->phone_number;
                                                    }
                                                }
                                            @endphp
                                            <tr>
                                                <td class="col-worker">
                                                    <div class="worker-name-cell">
                                                        <strong>{{ $worker->name_full }}</strong>
                                                        @if($position)
                                                            <span class="worker-position">— {{ $position }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="col-location">
                                                    @if($location)
                                                        <span class="location-cell">

                                                            {{ $location }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="col-phone">
                                                    @if(count($localPhones) > 0)
                                                        @foreach($localPhones as $phone)
                                                            <a href="tel:{{ $phone }}" class="phone-link local-phone">
                                                                {{ $phone }}
                                                            </a>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="col-phone">
                                                    @if(count($cityPhones) > 0)
                                                        @foreach($cityPhones as $phone)
                                                            <a href="tel:{{ $phone }}" class="phone-link city-phone">
                                                                {{ $phone }}
                                                            </a>
                                                        @endforeach
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
                    @endforeach

                    <!-- Пагинация -->
                    <div class="pagination-wrapper">
                        {{ $workers->appends([
                    'search' => request()->input('search'),
                    'dept_id' => request()->input('dept_id'),
                    'corpus_id' => request()->input('corpus_id')
                ])->links() }}
                    </div>
            @else
                <div class="empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="M21 21l-4.35-4.35" />
                    </svg>
                    <h4>Сотрудники не найдены</h4>
                    <p>Попробуйте изменить параметры поиска</p>
                </div>
            @endif
        </main>
    </div>
@endsection

<style>
    /* ============================================
   ОСНОВНОЙ КОНТЕЙНЕР
============================================ */
    .phone-book-wrapper {
        display: flex;
        gap: 30px;
    }

    /* ============================================
   БОКОВОЕ МЕНЮ (без изменений)
============================================ */
    .phone-book-sidebar {
        flex: 0 0 280px;
        position: sticky;
        top: 160px;
        max-height: calc(100vh - 160px);
        overflow-x: auto;
        padding-right: 32px;
    }

    .phone-book-sidebar::-webkit-scrollbar {
        width: 4px;
    }

    .phone-book-sidebar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }

    .phone-book-sidebar::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 2px;
    }

    .phone-book-sidebar::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Поиск */
    .search-box {
        margin-bottom: 24px;
    }

    .search-input-group {
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        transition: border-color 0.2s;
    }

    .search-input-group:focus-within {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .search-input {
        flex: 1;
        padding: 10px 14px;
        border: none;
        outline: none;
        font-size: 14px;
        color: #1a1a1a;
        background: transparent;
    }

    .search-input::placeholder {
        color: #999;
    }

    .search-btn {
        padding: 10px 14px;
        border: none;
        background: transparent;
        color: #666;
        cursor: pointer;
        transition: color 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .search-btn:hover {
        color: #2563eb;
    }

    /* Секции фильтров */
    .filter-section {
        margin-bottom: 28px;
    }

    .filter-title {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #888;
        margin-bottom: 12px;
        padding-left: 4px;
    }

    .filter-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .filter-menu>li {
        margin-bottom: 2px;
    }

    .filter-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        color: #444;
        text-decoration: none;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.2s;
        cursor: pointer;
        background: none;
        border: none;
        width: 100%;
    }

    .filter-link:hover {
        background: #f0f4ff;
        color: #2563eb;
    }

    .filter-link.active {
        background: #e8edff;
        color: #2563eb;
        font-weight: 500;
    }

    .filter-link svg {
        flex-shrink: 0;
        color: #888;
    }

    .filter-link.active svg {
        color: #2563eb;
    }

    .filter-link .arrow {
        margin-left: auto;
        font-size: 10px;
        color: #aaa;
        transition: transform 0.3s;
    }

    .filter-link:not(.collapsed) .arrow {
        transform: rotate(180deg);
    }

    .sub-menu {
        padding-left: 28px;
    }

    .sub-link {
        padding: 6px 12px;
        font-size: 13px;
        gap: 8px;
    }

    .sub-link svg {
        width: 14px;
        height: 14px;
    }

    .empty-message {
        display: block;
        padding: 6px 12px 6px 28px;
        font-size: 13px;
        color: #999;
        font-style: italic;
    }

    .reset-wrapper {
        padding-top: 12px;
        border-top: 1px solid #e8e8e8;
    }

    .reset-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        color: #dc2626;
        text-decoration: none;
        font-size: 13px;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .reset-btn:hover {
        background: #fef2f2;
        color: #b91c1c;
    }

    /* ============================================
   ОСНОВНОЙ КОНТЕНТ
============================================ */
    .phone-book-content {
        flex: 1;
        min-width: 0;
    }

    .content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .content-header h2 {
        font-size: 24px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0;
    }

    .count-badge {
        display: inline-block;
        padding: 4px 14px;
        background: #e8edff;
        color: #2563eb;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
    }

    .active-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .filter-label {
        font-size: 13px;
        color: #666;
        margin-right: 4px;
    }

    .filter-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 16px;
        font-size: 13px;
        font-weight: 500;
    }

    .filter-tag-search {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .filter-tag-dept {
        background: #e0f2fe;
        color: #0369a1;
    }

    .filter-tag-corpus {
        background: #dcfce7;
        color: #15803d;
    }

    /* ============================================
   ТАБЛИЦЫ ОТДЕЛОВ
============================================ */
    .dept-table-wrapper {
        margin-bottom: 32px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .dept-table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 20px;
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
    }

    .dept-table-header h3 {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0;
    }

    .dept-count {
        font-size: 13px;
        color: #666;
        background: #e8edff;
        padding: 2px 12px;
        border-radius: 12px;
    }

    .worker-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .worker-table thead {
        background: #f1f5f9;
    }

    .worker-table th {
        padding: 10px 16px;
        text-align: left;
        font-weight: 600;
        color: #444;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 1px solid #e2e8f0;
    }

    .worker-table td {
        padding: 10px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .worker-table tbody tr:hover {
        background: #f8fafc;
    }

    .worker-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Колонки */
    .col-worker {
        width: 40%;
        min-width: 250px;
    }

    .col-location {
        width: 25%;
        min-width: 180px;
    }

    .col-phone {
        width: 17.5%;
        min-width: 130px;
    }

    /* Ячейки */
    .worker-name-cell {
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 4px 6px;
    }

    .worker-name-cell strong {
        font-weight: 600;
        color: #1a1a1a;
    }

    .worker-position {
        font-size: 13px;
        color: #666;
    }

    .location-cell {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 13px;
        color: #555;
    }

    .location-cell svg {
        flex-shrink: 0;
        color: #888;
    }

    .phone-link {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 13px;
        text-decoration: none;
        transition: all 0.2s;
        margin: 1px 0;
    }

    .text-muted {
        color: #999;
    }

    /* ============================================
   ПУСТОЕ СОСТОЯНИЕ
============================================ */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .empty-state svg {
        color: #ccc;
        margin-bottom: 16px;
    }

    .empty-state h4 {
        font-size: 20px;
        color: #333;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #999;
        margin: 0;
    }

    /* ============================================
   ПАГИНАЦИЯ
============================================ */
    .pagination-wrapper {
        margin-top: 24px;
        display: flex;
        justify-content: center;
    }

    .pagination-wrapper .pagination {
        gap: 4px;
    }

    .pagination-wrapper .page-link {
        color: #444;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 8px 14px;
        transition: all 0.2s;
    }

    .pagination-wrapper .page-link:hover {
        background: #f0f4ff;
        border-color: #2563eb;
        color: #2563eb;
    }

    .pagination-wrapper .page-item.active .page-link {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    .pagination-wrapper .page-item.disabled .page-link {
        color: #bbb;
    }

    /* ============================================
   АДАПТИВНОСТЬ
============================================ */
    @media (max-width: 992px) {
        .phone-book-wrapper {
            flex-direction: column;
        }

        .phone-book-sidebar {
            flex: 1;
            position: static;
            max-height: none;
            padding-right: 0;
        }

        .col-worker {
            min-width: 180px;
        }

        .col-location {
            min-width: 140px;
        }

        .col-phone {
            min-width: 100px;
        }
    }

    @media (max-width: 768px) {
        .phone-book-wrapper {
            padding: 10px 0;
            gap: 20px;
        }

        .content-header h2 {
            font-size: 20px;
        }

        .dept-table-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
            padding: 12px 16px;
        }

        .dept-table-header h3 {
            font-size: 16px;
        }

        .worker-table {
            font-size: 13px;
        }

        .worker-table th,
        .worker-table td {
            padding: 8px 12px;
        }

        /* На мобильных делаем таблицу более компактной */
        .col-worker {
            min-width: 140px;
        }

        .col-location {
            min-width: 100px;
        }

        .col-phone {
            min-width: 80px;
        }

        .worker-name-cell {
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
        }

        .worker-position {
            font-size: 12px;
        }

        .worker-position::before {
            content: '';
            display: none;
        }

        .phone-link {
            font-size: 12px;
            padding: 1px 8px;
        }

        .active-filters {
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
        }
    }

    @media (max-width: 576px) {
        .worker-table {
            font-size: 12px;
        }

        .worker-table th,
        .worker-table td {
            padding: 6px 8px;
        }

        .col-worker {
            min-width: 120px;
        }

        .col-location,
        .col-phone {
            min-width: 80px;
        }

        .phone-link {
            font-size: 11px;
            padding: 1px 6px;
            display: block;
            text-align: center;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Функция для обновления URL с параметрами
        function updateUrl(params) {
            const url = new URL(window.location.href);
            url.search = '';

            Object.keys(params).forEach(key => {
                if (params[key] !== null && params[key] !== '' && params[key] !== undefined) {
                    url.searchParams.set(key, params[key]);
                }
            });

            window.location.href = url.toString();
        }

        // Функция для получения текущих параметров
        function getCurrentParams() {
            const params = {};
            const searchParams = new URLSearchParams(window.location.search);
            for (let [key, value] of searchParams.entries()) {
                params[key] = value;
            }
            return params;
        }

        // 1. Автоматический поиск при вводе
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            let timeoutId;
            searchInput.addEventListener('input', function () {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    const params = getCurrentParams();
                    if (this.value) {
                        params.search = this.value;
                    } else {
                        delete params.search;
                    }
                    updateUrl(params);
                }, 500);
            });
        }

        // 2. Обработка кликов по фильтрам
        document.querySelectorAll('[data-filter]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                const filterName = this.dataset.filter;
                const filterValue = this.dataset.value;
                const params = getCurrentParams();

                const search = document.getElementById('searchInput')?.value;
                if (search) {
                    params.search = search;
                } else {
                    delete params.search;
                }

                if (filterValue) {
                    params[filterName] = filterValue;
                } else {
                    delete params[filterName];
                }

                updateUrl(params);
            });
        });

        // 3. Обработка сброса фильтров
        document.querySelectorAll('[data-reset]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                const filterName = this.dataset.reset;
                const params = getCurrentParams();

                delete params[filterName];

                const search = document.getElementById('searchInput')?.value;
                if (search) {
                    params.search = search;
                } else {
                    delete params.search;
                }

                updateUrl(params);
            });
        });

        // 4. Обработка кнопки "Сбросить все"
        document.querySelector('[data-reset-all]')?.addEventListener('click', function (e) {
            e.preventDefault();
            window.location.href = this.href;
        });

        // 5. Раскрываем активные группы в меню
        document.querySelectorAll('.collapse').forEach(function (collapse) {
            if (collapse.querySelector('.filter-link.active')) {
                const toggle = document.querySelector('[href="#' + collapse.id + '"]');
                if (toggle) {
                    toggle.classList.remove('collapsed');
                    collapse.classList.add('show');
                }
            }
        });
    });
</script>
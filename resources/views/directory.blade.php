{{-- resources/views/directory.blade.php --}}
@extends('layouts.app')

@push('scripts')
    @vite('resources/js/directory.js')
@endpush

@section('title', 'Телефонный справочник')

@section('content')
    <div class="content-wrapper">
        <!-- Левое меню -->
        <aside class="sidebar-1">
            <!-- Поиск -->
            <div class="search-box">
                <span class="section-title-2">Поиск</span>
                <form method="GET" action="{{ route('directory') }}" id="searchForm">
                    <div class="search-input-group">
                        <input type="text" name="search" class="search-input"
                            placeholder="Поиск по ФИО, отделу, телефону..." value="{{ request()->input('search') }}"
                            id="searchInput">
                        <button class="button button--secondary" type="submit">
                            Найти
                        </button>
                        @if(request()->input('search') || request()->input('dept_id') || request()->input('corpus_id') || request()->input('floor'))
                            <div class="reset-wrapper">
                                <a href="{{ route('directory') }}" class="reset-btn" data-reset-all>
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

            <!-- Местоположение -->
            <div class="column-group">
                <span class="section-title-2">Меню</span>

                <div class="filter-section">
                    <h6 class="filter-title">Местоположение</h6>
                    <ul class="filter-menu">
                        <li>
                            <a href="#" data-reset="corpus_id" data-reset-floor="floor"
                                class="filter-link {{ !request()->input('corpus_id') && !request()->input('floor') ? 'active' : '' }}">
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
                                    <li class="menu-item">
                                        <div class="menu-toggle-wrapper">
                                            <a href="#"
                                                class="filter-link toggle-link {{ request()->input('corpus_id') && $building->corpuses->contains('id', request()->input('corpus_id')) ? 'active' : '' }}"
                                                data-toggle="building-{{ $building->id }}">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path
                                                        d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z">
                                                    </path>
                                                </svg>
                                                {{ $building->name }}
                                                <span class="arrow">▼</span>
                                            </a>
                                        </div>
                                        <ul class="sub-menu" id="building-{{ $building->id }}">
                                            @if($building->corpuses && $building->corpuses->count() > 0)
                                                @foreach($building->corpuses as $corpus)
                                                    @if($corpus instanceof \App\Models\Corpus)
                                                        <li class="menu-item">
                                                            <div class="menu-toggle-wrapper">
                                                                <a href="#" data-filter="corpus_id" data-value="{{ $corpus->id }}"
                                                                    class="filter-link toggle-link sub-link {{ request()->input('corpus_id') == $corpus->id ? 'active' : '' }}"
                                                                    data-toggle="corpus-{{ $corpus->id }}">
                                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2">
                                                                        <rect x="3" y="3" width="18" height="18" rx="2" />
                                                                        <path d="M3 9h18" />
                                                                        <path d="M9 3v18" />
                                                                    </svg>
                                                                    {{ $corpus->name }}
                                                                    @if($corpus->corpusDepts && $corpus->corpusDepts->count() > 0)
                                                                        <span class="arrow">▼</span>
                                                                    @endif
                                                                </a>
                                                            </div>
                                                            @if($corpus->corpusDepts && $corpus->corpusDepts->count() > 0)
                                                                <ul class="floor-sub-menu" id="corpus-{{ $corpus->id }}">
                                                                    @php
                                                                        $floors = $corpus->corpusDepts->pluck('floor')->unique()->sort();
                                                                    @endphp
                                                                    @foreach($floors as $floorNumber)
                                                                        <li>
                                                                            <a href="#" data-filter="floor" data-value="{{ $floorNumber }}"
                                                                                data-corpus-id="{{ $corpus->id }}"
                                                                                class="filter-link floor-link {{ request()->input('floor') == $floorNumber && request()->input('corpus_id') == $corpus->id ? 'active' : '' }}">
                                                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                                                    stroke="currentColor" stroke-width="2">
                                                                                    <rect x="2" y="2" width="20" height="20" rx="2" />
                                                                                    <path d="M2 8h20" />
                                                                                </svg>
                                                                                {{ $floorNumber }} этаж
                                                                            </a>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </li>
                                                    @endif
                                                @endforeach
                                            @else
                                                <li><span class="empty-message">Нет корпусов</span></li>
                                            @endif
                                        </ul>
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
                            <a href="#" data-reset="dept_id"
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
                                <li class="menu-item">
                                    <div class="menu-toggle-wrapper">
                                        <a href="#" class="filter-link toggle-link"
                                            data-toggle="dept-type-{{ Str::slug($typeName) }}">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <path
                                                    d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" />
                                            </svg>
                                            {{ $typeName ?: 'Без типа' }}
                                            <span class="arrow">▼</span>
                                        </a>
                                    </div>
                                    <ul class="sub-menu" id="dept-type-{{ Str::slug($typeName) }}">
                                        @foreach($depts as $dept)
                                            @if($dept instanceof \App\Models\Dept)
                                                <li>
                                                    <a href="#" data-filter="dept_id" data-value="{{ $dept->id }}"
                                                        class="filter-link sub-link {{ request()->input('dept_id') == $dept->id ? 'active' : '' }}">
                                                        {{ $dept->name_full }}
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        @else
                            <li><span class="empty-message">Нет отделов</span></li>
                        @endif
                    </ul>
                </div>
            </div>
        </aside>

        <!-- Основной контент -->
        <main class="main-content-1">
            <!-- Заголовок -->
            <div class="content-header">
                <span class="count-badge">{{ $workers->total() }} сотрудников</span>
            </div>

            <!-- Активные фильтры -->
            @if(request()->input('search') || request()->input('dept_id') || request()->input('corpus_id') || request()->input('floor'))
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
                    @if(request()->input('floor'))
                        <span class="filter-tag filter-tag-floor">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="2" width="20" height="20" rx="2" />
                                <path d="M2 8h20" />
                            </svg>
                            {{ request()->input('floor') }} этаж
                        </span>
                    @endif
                </div>
            @endif

            <!-- Список сотрудников в табличном виде -->
            @if($workers->count() > 0)
                @php
                    // Группируем сотрудников по отделам, сохраняя правильную сортировку
                    $groupedWorkers = collect();
                    
                    foreach($workers as $worker) {
                        $deptWorker = $worker->deptWorkers->first();
                        $dept = $deptWorker ? $deptWorker->dept : null;
                        $deptId = $dept ? $dept->id : 'no_dept';
                        
                        if (!$groupedWorkers->has($deptId)) {
                            $groupedWorkers[$deptId] = collect();
                        }
                        $groupedWorkers[$deptId]->push($worker);
                    }
                    
                    // Сортируем группы по spr_order отдела (по возрастанию)
                    $groupedWorkers = $groupedWorkers->sortBy(function ($group) {
                        $dept = $group->first()->deptWorkers->first()?->dept;
                        return $dept ? (int)$dept->spr_order : PHP_INT_MAX;
                    });
                @endphp

                @foreach($groupedWorkers as $deptId => $deptWorkers)
                    @php
                        $dept = $deptWorkers->first()->deptWorkers->first()?->dept;
                        $deptName = $dept ? $dept->name_full : 'Без отдела';
                        $deptShort = $dept ? $dept->name_short : 'Без отдела';
                        $deptSprOrder = $dept ? $dept->spr_order : null;
                        
                        // Сортируем сотрудников внутри отдела по spr_order (по возрастанию)
                        $deptWorkers = $deptWorkers->sortBy(function($worker) {
                            return (int)$worker->spr_order;
                        });
                    @endphp
                    
                    <div class="section-title-1 mb-4">
                        {{ $deptName }}
                    </div>
                    
                    <div class="table-wrapper-1">
                        <div class="table-responsive">
                            <table class="data-table">
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
                                                <div class="cell-name">
                                                    <strong>{{ $worker->name_full }}</strong>
                                                    @if($position)
                                                        <span class="cell-description">— {{ $position }}</span>
                                                    @endif
                                              
                                                </div>
                                            </td>
                                            <td class="col-location">
                                                @if($location)
                                                    <span class="cell-location">
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
                        'corpus_id' => request()->input('corpus_id'),
                        'floor' => request()->input('floor')
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
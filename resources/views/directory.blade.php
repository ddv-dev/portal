{{-- resources/views/phone-book/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Телефонный справочник')

@section('content')
<div class="phone-book-container">
    <div class="row">
        <!-- Левое меню -->
        <div class="col-md-3">
            <div class="phone-book-sidebar">
                <!-- Поиск -->
                <div class="search-box mb-4">
                    <form method="GET" action="{{ route('directory') }}">
                        <div class="input-group">
                            <input 
                                type="text" 
                                name="search" 
                                class="form-control" 
                                placeholder="Поиск по ФИО, отделу, телефону..."
                                value="{{ request()->input('search') }}"
                            >
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        @if(request()->input('search'))
                            <a href="{{ route('directory') }}" class="btn btn-link btn-sm mt-2">
                                <i class="fas fa-times"></i> Сбросить фильтры
                            </a>
                        @endif
                    </form>
                </div>

                <!-- Поиск по местоположению -->
                <div class="location-filter mb-4">
                    <h6 class="sidebar-title">Местоположение</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="{{ route('directory') }}" 
                               class="nav-link {{ !request()->input('corpus_id') ? 'active' : '' }}">
                                <i class="fas fa-building"></i> Все здания
                            </a>
                        </li>
                        @foreach($buildings as $building)
                            <li class="nav-item">
                                <a href="#building-{{ $building->id }}" 
                                   class="nav-link collapsed" 
                                   data-bs-toggle="collapse"
                                   role="button">
                                    <i class="fas fa-building"></i> {{ $building->name }}
                                </a>
                                <div class="collapse" id="building-{{ $building->id }}">
                                    <ul class="nav flex-column ms-3">
                                        @foreach($building->corpuses as $corpus)
                                            <li class="nav-item">
                                                <a href="{{ route('directory', ['corpus_id' => $corpus->id]) }}" 
                                                   class="nav-link {{ request()->input('corpus_id') == $corpus->id ? 'active' : '' }}">
                                                    <i class="fas fa-door-open"></i> {{ $corpus->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Поиск по отделам -->
                <div class="department-filter">
                    <h6 class="sidebar-title">Отделения</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="{{ route('directory') }}" 
                               class="nav-link {{ !request()->input('dept_id') ? 'active' : '' }}">
                                <i class="fas fa-list"></i> Все отделы
                            </a>
                        </li>
                        @foreach($departments as $typeName => $depts)
                            <li class="nav-item">
                                <a href="#dept-type-{{ Str::slug($typeName) }}" 
                                   class="nav-link collapsed" 
                                   data-bs-toggle="collapse"
                                   role="button">
                                    <i class="fas fa-folder"></i> {{ $typeName ?: 'Без типа' }}
                                </a>
                                <div class="collapse" id="dept-type-{{ Str::slug($typeName) }}">
                                    <ul class="nav flex-column ms-3">
                                        @foreach($depts as $dept)
                                            <li class="nav-item">
                                                <a href="{{ route('directory', ['dept_id' => $dept->id]) }}" 
                                                   class="nav-link {{ request()->input('dept_id') == $dept->id ? 'active' : '' }}">
                                                    {{ $dept->name_short ?: $dept->name_full }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Основной контент -->
        <div class="col-md-9">
            <div class="phone-book-content">
                <!-- Заголовок с количеством -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Телефонный справочник</h2>
                    <span class="badge bg-secondary">{{ $workers->total() }} сотрудников</span>
                </div>

                <!-- Список сотрудников -->
                @if($workers->count() > 0)
                    <div class="workers-list">
                        @foreach($workers as $worker)
                            @php
                                $deptWorker = $worker->deptWorkers->first();
                                $dept = $deptWorker?->dept;
                                $corpusDept = $deptWorker?->corpusDept;
                                $corpus = $corpusDept?->corpus;
                                $building = $corpus?->building;
                                
                                $location = [];
                                if ($building) $location[] = $building->name;
                                if ($corpus) $location[] = $corpus->name;
                                if ($corpusDept && $corpusDept->floor) $location[] = $corpusDept->floor . ' этаж';
                                if ($corpusDept && $corpusDept->sector) $location[] = 'сектор ' . $corpusDept->sector;
                                $locationStr = implode(' → ', $location);
                            @endphp
                            
                            <div class="worker-card card mb-3">
                                <div class="card-body">
                                    <div class="row align-items-start">
                                        <div class="col-md-7">
                                            <h5 class="card-title mb-1">
                                                <i class="fas fa-user-circle text-primary"></i>
                                                {{ $worker->name_full }}
                                            </h5>
                                            <div class="worker-info">
                                                @if($deptWorker?->position)
                                                    <span class="badge bg-info me-2">{{ $deptWorker->position }}</span>
                                                @endif
                                                @if($dept)
                                                    <span class="text-muted me-2">
                                                        <i class="fas fa-building"></i> {{ $dept->name_full }}
                                                    </span>
                                                @endif
                                                @if($locationStr)
                                                    <span class="text-muted">
                                                        <i class="fas fa-map-marker-alt"></i> {{ $locationStr }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($worker->login_ad)
                                                <small class="text-muted">
                                                    <i class="fas fa-user-tag"></i> {{ $worker->login_ad }}
                                                </small>
                                            @endif
                                        </div>
                                        <div class="col-md-5">
                                            <div class="worker-phones">
                                                @forelse($worker->phones as $phone)
                                                    <div class="phone-item">
                                                        <a href="tel:{{ $phone->phone_number }}" 
                                                           class="btn btn-outline-success btn-sm me-2 mb-1">
                                                            <i class="fas fa-phone"></i> {{ $phone->phone_number }}
                                                        </a>
                                                        @if($phone->is_city_number)
                                                            <span class="badge bg-secondary">городской</span>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <span class="text-muted">Нет телефонов</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Пагинация -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $workers->appends([
                            'search' => request()->input('search'),
                            'dept_id' => request()->input('dept_id'),
                            'corpus_id' => request()->input('corpus_id')
                        ])->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>Сотрудники не найдены</h4>
                        <p class="text-muted">Попробуйте изменить параметры поиска</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .phone-book-sidebar {
        position: sticky;
        top: 20px;
        max-height: calc(100vh - 100px);
        overflow-y: auto;
    }
    
    .phone-book-sidebar::-webkit-scrollbar {
        width: 6px;
    }
    
    .phone-book-sidebar::-webkit-scrollbar-thumb {
        background: #ddd;
        border-radius: 3px;
    }
    
    .sidebar-title {
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        color: #6c757d;
        letter-spacing: 0.5px;
        margin-bottom: 15px;
    }
    
    .phone-book-sidebar .nav-link {
        padding: 8px 12px;
        color: #495057;
        border-radius: 8px;
        transition: all 0.2s;
        font-size: 14px;
    }
    
    .phone-book-sidebar .nav-link:hover {
        background: #f8f9fa;
        color: #0d6efd;
    }
    
    .phone-book-sidebar .nav-link.active {
        background: #e7f1ff;
        color: #0d6efd;
        font-weight: 500;
    }
    
    .phone-book-sidebar .nav-link .fas {
        width: 20px;
        margin-right: 8px;
    }
    
    .phone-book-sidebar .nav-link.collapsed .fas.fa-chevron-down {
        transform: rotate(-90deg);
    }
    
    .phone-book-sidebar .collapse .nav-link {
        padding-left: 32px;
        font-size: 13px;
    }
    
    .worker-card {
        transition: all 0.2s;
        border-left: 4px solid #0d6efd;
    }
    
    .worker-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transform: translateX(4px);
    }
    
    .worker-info {
        margin-top: 8px;
    }
    
    .worker-info .badge {
        font-size: 12px;
        font-weight: 400;
    }
    
    .worker-phones {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .worker-phones .phone-item {
        display: inline-block;
    }
    
    .worker-phones .btn-outline-success {
        border-radius: 20px;
        font-size: 13px;
        padding: 4px 14px;
    }
    
    .search-box .btn-primary {
        border-radius: 0 8px 8px 0;
    }
    
    .search-box .form-control {
        border-radius: 8px 0 0 8px;
        border-right: none;
    }
    
    .search-box .form-control:focus {
        box-shadow: none;
        border-color: #dee2e6;
    }
    
    /* Адаптивность */
    @media (max-width: 768px) {
        .phone-book-sidebar {
            position: static;
            max-height: none;
            margin-bottom: 30px;
        }
        
        .worker-card .col-md-7,
        .worker-card .col-md-5 {
            width: 100%;
        }
        
        .worker-card .col-md-5 {
            margin-top: 15px;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            let timeoutId;
            searchInput.addEventListener('input', function() {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    this.closest('form').submit();
                }, 500);
            });
        }
    });
</script>
@endsection
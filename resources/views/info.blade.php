{{-- resources/views/info.blade.php --}}
@extends('layouts.app')

@section('title', 'Информация')

@section('content')
    <!-- Конец отладки -->
    <div class="content-wrapper">
        <!-- Левое меню -->
        <aside class="sidebar-1">
            <!-- Поиск -->
            <div class="search-box">
                <span class="section-title-2">Поиск</span>
                <form method="GET" action="{{ route('info') }}" id="searchForm">
                    <div class="search-input-group">
                        <input type="text" name="search" class="search-input" placeholder="Поиск по названию..."
                            value="{{ request()->input('search') }}" id="searchInput">
                        <button class="button button--secondary" type="submit">
                            Найти
                        </button>
                        @if(request()->input('search') || request()->input('type') || request()->input('year'))
                            <div class="reset-wrapper">
                                <a href="{{ route('info') }}" class="reset-btn" data-reset-all>
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

                <!-- Типы информации -->
                @if($infoTypesWithCount->count() > 0)
                    <div class="filter-section">
                        <h6 class="filter-title">Разделы</h6>
                        <ul class="filter-menu">
                            <li>
                                <a href="#" data-reset="type"
                                    class="filter-link {{ !request()->input('type') ? 'active' : '' }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <rect x="2" y="2" width="20" height="20" rx="2" />
                                        <path d="M2 8h20" />
                                        <path d="M8 2v20" />
                                    </svg>
                                    Все разделы
                                </a>
                            </li>
                            @foreach($infoTypesWithCount as $type)
                                <li>
                                    <a href="#" data-filter="type" data-value="{{ $type['id'] }}"
                                        class="filter-link {{ request()->input('type') == $type['id'] ? 'active' : '' }}">
                      
                                        {{ $type['name'] }}
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
            @if(request()->input('search') || request()->input('type'))
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

                    @if(request()->input('type'))
                        <span class="filter-tag filter-tag-dept">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                            </svg>
                            @php
                                $typeName = $infoTypesList[request()->input('type')] ?? 'Неизвестный тип';
                            @endphp
                            {{ $typeName }}
                        </span>
                    @endif

                </div>
            @endif

            <!-- Таблица информации -->
            @if($infos->count() > 0)
                <div class="table-wrapper-1">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="col-info-title">Заголовок</th>
                                    <th class="col-info-type">Тип</th>
                                    <th class="col-info-date">Дата</th>
                                    <th class="col-info-attachments">Вложения</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($infos as $info)
                                    <tr>
                                        <td class="col-info-title">
                                            <div class="cell-name">
                                                <strong>{{ $info->title }}</strong>
                                            </div>
                                        </td>
                                        <td class="col-info-type">
                                            <span class="type-badge">
                                                {{ $info->type?->name ?? 'Без типа' }}
                                            </span>
                                        </td>
                                        <td class="col-info-date">
                                            @if($info->created_at)
                                                <span class="cell-location">
                                                    {{ \Carbon\Carbon::parse($info->created_at)->format('d.m.Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="col-info-attachments">
                                            @if($info->attachments->count() > 0)
                                                <div class="attachment-list">
                                                    @foreach($info->attachments as $attachment)
                                                        <a href="{{ route('download.attachment', ['model' => 'info', 'id' => $info->id, 'attachment' => $attachment->id]) }}"
                                                            class="attachment-link" title="{{ $attachment->name }}">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2">
                                                                <path
                                                                    d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48" />
                                                            </svg>
                                                            {{ Str::limit($attachment->name, 20) }}
                                                        </a>
                                                        @if(!$loop->last)<br>@endif
                                                    @endforeach
                                                </div>
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
                    {{ $infos->appends(request()->query())->links() }}
                </div>
            @else
                <div class="empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="M21 21l-4.35-4.35" />
                    </svg>
                    <h4>Информация не найдена</h4>
                    <p>Попробуйте изменить параметры поиска</p>
                </div>
            @endif
        </main>
    </div>
@endsection
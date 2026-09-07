@extends('layouts.app')
@section('title', 'Дни рождения сотрудников')

@section('content')
    <div class="content-wrapper">
        <main class="main-content-1">
            <!-- Навигация по месяцам -->


            <!-- Поиск -->

            <div class="search-container">
                <div class="months-navigation mb-4">
                    <div class="btn-group" role="group" aria-label="Месяцы">
                        @foreach($months as $month)
                            <a href="{{ route('birthdays', ['month' => $month->month_number, 'search' => request('search')]) }}"
                                class="btn-month {{ $selectedMonth == $month->month_number ? 'btn-month--active' : '' }} {{ $currentMonth == $month->month_number ? 'btn-month--current' : '' }}">
                                {{ $month->month_name }}

                            </a>
                        @endforeach
                    </div>


                </div>
                <div>
                    <form method="GET" action="{{ route('birthdays') }}" id="searchForm">
                        <input type="text" name="search" class="search-input"
                            placeholder="Поиск по ФИО, отделу, телефону..." value="{{ request()->input('search') }}"
                            id="searchInput">
                        <button class="button button--secondary" type="submit">
                            Найти
                        </button>

                    </form>

                </div>
            </div>



            <!-- Активные фильтры -->
            @if(request()->input('search'))
                <div class="active-filters mb-3">
                    <span class="filter-label">Активные фильтры:</span>
                    <span class="filter-tag filter-tag-search">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <path d="M21 21l-4.35-4.35" />
                        </svg>
                        {{ request()->input('search') }}
                    </span>
                </div>
            @endif

            <!-- Таблица сотрудников -->
            @if($workers->count() > 0)
                <div class="table-wrapper-1">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="col-primary">ФИО</th>
                                    <th class="col-tertiary">Должность</th>
                                    <th class="col-tertiary">Отделение</th>
                                    <th class="col-tertiary">Дата рождения</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($workers as $worker)
                                    <tr>
                                        <td class="col-order-number">
                                            <div class="cell-name">
                                                <strong>{{ $worker->name_full ?: '—' }}</strong>
                                                @if ($worker->isBirthdayToday())
                                                    <span style="margin-left:auto">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M6.33333 15.509H0.5V4.25903H2.16667M6.33333 15.509V9.45367M6.33333 15.509H9.66667M9.66667 15.509H15.5V4.25903H13.8333M9.66667 15.509V8.4257M13.8333 4.25903V0.509033C10.2961 0.572008 8.96817 1.33659 8 4.25903M13.8333 4.25903H9.66667M8 4.25903C6.99459 1.20782 5.61919 0.509112 2.16667 0.509033V4.25903M8 4.25903H5.91667M8 4.25903C7.74475 7.73875 6.9989 9.33433 4.25 11.3424L2.58333 8.4257C4.51329 7.35811 5.46749 6.49587 5.91667 4.25903M8 4.25903C8.30511 7.20766 8.90765 8.43013 11.3333 9.25903L12.5833 7.1757C11.0503 6.46296 10.4772 5.75361 9.66667 4.25903M8 4.25903H9.66667M2.16667 4.25903H5.91667"
                                                                stroke="#FA792F" stroke-linecap="round" />
                                                        </svg>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <td class="col-order-status">
                                            @if($worker->depts->first())
                                                <strong>{{ $worker->depts->first()->pivot->position ?: '—' }}</strong>
                                            @else
                                                <strong>—</strong>
                                            @endif
                                        </td>

                                        <td class="col-order-status">
                                            @if($worker->depts->first())
                                                <strong>{{ $worker->depts->first()->name_short ?: '—' }}</strong>
                                            @else
                                                <strong>—</strong>
                                            @endif
                                        </td>
                                        <td class="col-order-date">
                                            @if($worker->birthday())
                                                <span class="cell-location">
                                                    {{ $worker->birthday() }}
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
                    {{ $workers->appends(request()->query())->links() }}
                </div>
            @else
                <div class="empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="M21 21l-4.35-4.35" />
                    </svg>
                    <h4>Сотрудники не найдены</h4>
                    <p>В этом месяце нет дней рождения или попробуйте изменить параметры поиска</p>
                </div>
            @endif
        </main>
    </div>

    <style>
        .months-navigation {
            overflow-x: auto;
            white-space: nowrap;
            margin-right: auto;
            background: white;
            border-radius: 20px;
            padding: 12px 24px;
        }

        .months-navigation .btn-group {
            flex-wrap: nowrap;
            display: flex;
            gap: 14px;
        }



        .badge-success {
            display: inline-block;
            margin-left: 10px;
            padding: 4px 10px;
            font-size: 12px;
            background-color: #28a745;
            color: white;
            border-radius: 12px;
        }

        .search-wrapper form {
            max-width: 500px;
            display: flex;
            gap: 10px;
        }

        .search-wrapper .form-control {
            flex: 1;
            padding: 8px 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .search-wrapper .btn {
            padding: 8px 20px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }

        .search-wrapper .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .search-wrapper .btn-secondary {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
        }

        .active-filters {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 13px;
        }

        .filter-tag-search {
            background-color: #e3f2fd;
            color: #1976d2;
        }
    </style>
@endsection
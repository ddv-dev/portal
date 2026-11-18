{{-- resources/views/duty/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Графики дежурств')

@push('styles')
<style>
    /* Локальные стили для календаря дежурств */
    .duty-controls {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        margin-bottom: var(--spacing-lg);
        flex-wrap: wrap;
    }
    .duty-controls select {
        min-width: 120px;
        height: 40px;
        border-radius: var(--border-radius-sm);
        border: 1px solid var(--color-border);
        padding: 0 var(--spacing-md);
        font-family: var(--font-family);
        font-size: var(--font-size-xs);
        color: var(--color-text);
        background: #fff;
        cursor: pointer;
    }
    .duty-controls select:focus {
        outline: none;
        border-color: var(--color-primary);
    }
    .duty-controls .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: var(--border-radius-sm);
        background: #fff;
        border: 1px solid var(--color-border);
        color: var(--color-primary);
        cursor: pointer;
        transition: all var(--transition-fast);
    }
    .duty-controls .btn-icon:hover {
        background: var(--color-white-blue);
        border-color: var(--color-primary-light);
    }
    .duty-last-updated {
        margin-left: auto;
        text-align: right;
        font-size: 12px;
        color: #999;
        line-height: 1.4;
    }
    .duty-last-updated strong {
        display: block;
        color: #333;
        font-weight: 500;
        font-size: 14px;
    }

    /* Таблица */
    .duty-table th {
        text-transform: none; /* Убираем капс из общего стиля, как на скрине */
        font-weight: 500;
        color: #666;
        font-size: 13px;
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 16px;
    }
    .duty-table td {
        padding: 14px 16px;
        font-size: 13px;
        color: #333;
        border-bottom: 1px solid #f1f5f9;
        line-height: 1.4;
    }
    .duty-table td.weekend {
        background-color: #fdf2f2; /* Легкий розовый для выходных */
    }
    .duty-table .col-day {
        width: 50px;
        text-align: center;
        font-weight: 500;
        color: #1a1a1a;
    }
    .duty-table .col-weekday {
        width: 100px;
        color: #666;
    }
    .duty-table .duty-person {
        margin-bottom: 4px;
    }
    .duty-table .duty-person:last-child {
        margin-bottom: 0;
    }
</style>
@endpush

@section('content')
    <div class="section" style="padding-top: var(--spacing-md);">
        <h1 style="color: #4668aa; font-size: 28px; margin-bottom: var(--spacing-lg);">Графики дежурств</h1>

        <!-- Панель управления (Месяц, Год, Печать) -->
        <div class="duty-controls">
            <form method="GET" action="{{ route('duty') }}" id="filterForm" style="display: flex; gap: 8px; margin: 0;">
                <!-- Месяц -->
                <select name="month" onchange="this.form.submit()">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected($m == $month)>
                            {{ \Illuminate\Support\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                
                <!-- Год -->
                <select name="year" onchange="this.form.submit()">
                    @for ($y = now()->year - 2; $y <= now()->year + 2; $y++)
                        <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                    @endfor
                </select>
            </form>

            <!-- Кнопка печати (декоративная) -->
            <button type="button" class="btn-icon" onclick="window.print()" title="Распечатать">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
            </button>

            <div class="duty-last-updated">
                Последнее изменение
                <strong>{{ now()->format('d.m.Y H:i') }}</strong>
            </div>
        </div>

        <!-- Таблица дежурств -->
        <div class="table-wrapper-1">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="data-table duty-table">
                    <thead>
                        <tr>
                            <th class="col-day">Число ↓</th>
                            <th class="col-weekday">День недели</th>
                            @foreach ($roles as $role)
                                <th>{{ $role->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($days as $day)
                            <tr>
                                <td class="col-day">
                                    {{ $day['day'] }}
                                </td>
                                <td class="col-weekday">
                                    {{ $day['weekday_ru'] }}
                                </td>

                                @foreach ($roles as $role)
                                    @php $cell = $day['cells'][$role->id] ?? []; @endphp
                                    <td @class(['weekend' => $day['is_weekend']])>
                                        @if (empty($cell))
                                            <span style="color: #999;">–</span> {{-- Обычный прочерк как на скрине --}}
                                        @else
                                            @foreach ($cell as $person)
                                                <div class="duty-person">
                                                    
                                                    @if (!empty($person['number']))
                                                        <span style="color: #666;">{{ $person['number'] }} -</span>
                                                    @endif
                                                    {{ $person['fullname'] }}
                                                </div>
                                            @endforeach
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
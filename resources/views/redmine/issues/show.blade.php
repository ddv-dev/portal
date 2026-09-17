@extends('layouts.app')
@section('title', 'Задача #' . $issue->id)

@section('content')
<div class="rm-issue-page">

    <div class="rm-issue-page__head">
        <a href="{{ url()->previous() }}" class="rm-back">← Назад</a>
        <h1 class="rm-title">#{{ $issue->id }} — {{ $issue->subject }}</h1>
    </div>

    @if(session('ok'))
    <div class="rm-alert rm-alert--ok">{{ session('ok') }}</div>
    @endif
    @if($errors->any())
    <div class="rm-alert rm-alert--err">
        @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
    @endif

    {{-- МЕТАДАННЫЕ --}}
    <section class="rm-card">
        <table class="rm-meta">
            <tr>
                <th>Проект</th>
                <td>{{ $issue->project?->name ?? '—' }}</td>
                <th>Статус</th>
                <td>{{ $issue->status?->name ?? '—' }}</td>
            </tr>
            <tr>
                <th>Трекер</th>
                <td>{{ $issue->tracker?->name ?? '—' }}</td>
                <th>Приоритет</th>
                <td>{{ $issue->priority?->name ?? '—' }}</td>
            </tr>
            <tr>
                <th>Автор</th>
                <td>
                    @if($issue->author)
                    {{ trim(($issue->author->lastname ?? '') . ' ' . ($issue->author->firstname ?? '')) ?: $issue->author->login }}
                    @else
                    —
                    @endif
                </td>
                <th>Исполнитель</th>
                <td>
                    @if($issue->assignee)
                    {{ trim(($issue->assignee->lastname ?? '') . ' ' . ($issue->assignee->firstname ?? '')) ?: $issue->assignee->login }}
                    @else
                    —
                    @endif
                </td>
            </tr>
            <tr>
                <th>Начало</th>
                <td>{{ $issue->start_date?->format('d.m.Y') ?? '—' }}</td>
                <th>Срок</th>
                <td>{{ $issue->due_date?->format('d.m.Y') ?? '—' }}</td>
            </tr>
            <tr>
                <th>Создано</th>
                <td>{{ $issue->created_on?->format('d.m.Y H:i') ?? '—' }}</td>
                <th>Обновлено</th>
                <td>{{ $issue->updated_on?->format('d.m.Y H:i') ?? '—' }}</td>
            </tr>
            @if($issue->closed_on)
            <tr>
                <th>Закрыто</th>
                <td colspan="3">{{ $issue->closed_on->format('d.m.Y H:i') }}</td>
            </tr>
            @endif
        </table>
    </section>
    @if($customValues->isNotEmpty())
    <section class="rm-card">
        <h3 class="rm-card__title">Дополнительные поля</h3>
        <table class="rm-meta">
            @foreach($customValues as $cv)
            <tr>
                <th>{{ $cv->customField?->name ?? '—' }}</th>
                <td>
                    @php $v = $cv->value; @endphp
                    @if($cv->customField?->field_format === 'bool')
                    {{ $v == '1' ? 'Да' : 'Нет' }}
                    @elseif($cv->customField?->field_format === 'date')
                    {{ \Illuminate\Support\Carbon::parse($v)->format('d.m.Y') }}
                    @else
                    {{ $v }}
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
    </section>
    @endif
    {{-- ОПИСАНИЕ --}}
    <section class="rm-card">
        <h3 class="rm-card__title">Описание</h3>
        @if($issue->description)
        <div class="rm-description">{!! nl2br(e($issue->description)) !!}</div>
        @else
        <p class="rm-muted">Описание отсутствует.</p>
        @endif
    </section>

    {{-- ИСТОРИЯ --}}
    <section class="rm-card">
        <h3 class="rm-card__title">История</h3>

        @if($history->isEmpty())
        <p class="rm-muted">Записей пока нет.</p>
        @else
        <ul class="rm-history">
            @foreach($history as $journal)
            <li class="rm-history__item">
                <div class="rm-history__head">
                    <span class="rm-history__author">
                        @if($journal->user)
                        {{ trim(($journal->user->lastname ?? '') . ' ' . ($journal->user->firstname ?? '')) ?: $journal->user->login }}
                        @else
                        Система
                        @endif
                    </span>
                    <span class="rm-history__date">
                        {{ $journal->created_on?->format('d.m.Y H:i') }}
                    </span>
                </div>

                @if($journal->notes)
                <div class="rm-history__notes">{!! nl2br(e($journal->notes)) !!}</div>
                @endif

                @if($journal->details->isNotEmpty())
                <ul class="rm-history__changes">
                    @foreach($journal->details as $d)
                    <li>
                        <span class="rm-history__prop">{{ $d->prop_key }}</span>:
                        <span class="rm-history__old">{{ $d->old_value ?? '—' }}</span>
                        →
                        <span class="rm-history__new">{{ $d->value ?? '—' }}</span>
                    </li>
                    @endforeach
                </ul>
                @endif
            </li>
            @endforeach
        </ul>
        @endif
    </section>

    {{-- ФОРМА КОММЕНТАРИЯ --}}
    <section class="rm-card">
        <h3 class="rm-card__title">Добавить комментарий</h3>
        <form method="POST" action="{{ route('redmine.issues.comment', $issue->id) }}" class="rm-form">
            @csrf
            <label class="rm-form__full">Комментарий
                <textarea name="notes" rows="4" required>{{ old('notes') }}</textarea>
            </label>
            <label class="rm-checkbox">
                <input type="checkbox" name="private" value="1" @checked(old('private'))>
                Приватный комментарий
            </label>
            <div class="rm-form__actions">
                <button type="submit" class="rm-btn rm-btn--primary">Отправить</button>
            </div>
        </form>
    </section>
</div>
@endsection

@push('styles')
<style>
    .rm-issue-page {
        max-width: 960px;
    }

    .rm-issue-page__head {
        margin-bottom: 16px;
    }

    .rm-back {
        display: inline-block;
        margin-bottom: 8px;
        color: #1a56db;
        text-decoration: none;
        font-size: 13px;
    }

    .rm-back:hover {
        text-decoration: underline;
    }

    .rm-title {
        margin: 0;
    }

    .rm-muted {
        color: #777;
    }

    .rm-card {
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
        background: #fff;
    }

    .rm-card__title {
        margin: 0 0 12px;
    }

    .rm-meta {
        width: 100%;
        border-collapse: collapse;
    }

    .rm-meta th,
    .rm-meta td {
        padding: 6px 8px;
        text-align: left;
        font-size: 14px;
        vertical-align: top;
    }

    .rm-meta th {
        color: #666;
        font-weight: 600;
        width: 120px;
    }

    .rm-description {
        font-size: 14px;
        line-height: 1.5;
        color: #333;
    }

    .rm-history {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .rm-history__item {
        border-top: 1px solid #eee;
        padding: 12px 0;
    }

    .rm-history__item:first-child {
        border-top: 0;
        padding-top: 0;
    }

    .rm-history__head {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: #777;
        margin-bottom: 6px;
    }

    .rm-history__author {
        font-weight: 600;
        color: #444;
    }

    .rm-history__notes {
        font-size: 14px;
        color: #333;
        line-height: 1.5;
    }

    .rm-history__changes {
        margin: 6px 0 0;
        padding-left: 18px;
        font-size: 13px;
        color: #555;
    }

    .rm-history__prop {
        color: #1a56db;
        font-weight: 600;
    }

    .rm-history__old {
        color: #a3170b;
    }

    .rm-history__new {
        color: #1e6b34;
    }

    .rm-form {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .rm-form__full {
        display: flex;
        flex-direction: column;
        gap: 4px;
        font-size: 13px;
        color: #555;
    }

    .rm-form textarea {
        padding: 6px 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        font-family: inherit;
    }

    .rm-checkbox {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #555;
    }

    .rm-form__actions {
        display: flex;
        gap: 8px;
    }

    .rm-btn {
        padding: 8px 16px;
        border-radius: 4px;
        border: 1px solid transparent;
        cursor: pointer;
        font-size: 14px;
    }

    .rm-btn--primary {
        background: #1a56db;
        color: #fff;
    }

    .rm-btn--primary:hover {
        background: #1648b8;
    }

    .rm-alert {
        padding: 8px 12px;
        border-radius: 4px;
        margin-bottom: 12px;
        font-size: 14px;
    }

    .rm-alert--ok {
        background: #e6f4ea;
        color: #1e6b34;
    }

    .rm-alert--err {
        background: #fdecea;
        color: #a3170b;
    }
</style>
@endpush
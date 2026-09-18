@extends('layouts.app')
@section('title', 'Обращения в подразделения больницы')

@section('content')
<div class="redmine-appeals">

    {{-- ЛЕВАЯ КОЛОНКА: список проектов --}}
    <aside class="rm-sidebar">
        <h2 class="rm-sidebar__title">Проекты</h2>

        @if($projects->isEmpty())
        <p class="rm-muted">Нет доступных проектов.</p>
        @else
        <ul class="rm-projects">
            @foreach($projects as $project)
            <li>
                <a href="{{ route('redmine.appeals', ['project' => $project->id, 'filter' => $filter]) }}"
                    class="rm-project {{ $selectedProject && $selectedProject->id === $project->id ? 'is-active' : '' }}">
                    {{ $project->name }}
                </a>
            </li>
            @endforeach
        </ul>
        @endif
    </aside>

    {{-- ПРАВАЯ КОЛОНКА: форма + история --}}
    <main class="rm-main">
        @if(!$selectedProject)
        <p class="rm-muted">Выберите проект слева.</p>
        @else
        <h1 class="rm-title">{{ $selectedProject->name }}</h1>
        @if($selectedProject->description)
        <p class="rm-muted">{{ \Illuminate\Support\Str::limit($selectedProject->description, 200) }}</p>
        @endif

        {{-- ФОРМА СОЗДАНИЯ ЗАДАЧИ --}}
        <section class="rm-card">
            <h3 class="rm-card__title">Создать задачу</h3>

            @if(session('ok'))
            <div class="rm-alert rm-alert--ok">{{ session('ok') }}</div>
            @endif
            @if($errors->any())
            <div class="rm-alert rm-alert--err">
                @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('redmine.issues.store') }}" class="rm-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="project_id" value="{{ $selectedProject->id }}">

                <div class="rm-form__row">
                    <label>Трекер
                        <select name="tracker_id" required>
                            @foreach($trackers as $t)
                            <option value="{{ $t->id }}" @selected(old('tracker_id')==$t->id)>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <label class="rm-form__full">Тема
                    <input type="text" name="subject" required maxlength="255" value="{{ old('subject') }}">
                </label>

                <label class="rm-form__full">Описание
                    <textarea name="description" rows="6">{{ old('description') }}</textarea>
                </label>

                <label class="rm-form__full">Вложения
                    <input type="file" name="attachments[]" multiple>
                </label>

                <div id="rm-custom-fields" class="rm-form__custom">
                    @include('redmine.partials.custom-fields', ['customFields' => $customFields])
                </div>
                <div class="rm-form__row">
                    <label>Начало
                        <input type="date" name="start_date" value="{{ old('start_date') }}">
                    </label>
                    <label>Срок
                        <input type="date" name="due_date" value="{{ old('due_date') }}">
                    </label>
                </div>

                <div class="rm-form__actions">
                    <button type="submit" class="rm-btn rm-btn--primary">Создать задачу</button>
                </div>
            </form>
        </section>

        {{-- ИСТОРИЯ ЗАДАЧ --}}
        <section class="rm-card">
            <div class="rm-card__head">
                <h3 class="rm-card__title">Мои задачи в этом проекте</h3>

                <nav class="rm-tabs">
                    <a href="{{ route('redmine.appeals', ['project' => $selectedProject->id, 'filter' => 'open']) }}"
                        class="rm-tab {{ $filter === 'open' ? 'is-active' : '' }}">Открытые</a>
                    <a href="{{ route('redmine.appeals', ['project' => $selectedProject->id, 'filter' => 'closed']) }}"
                        class="rm-tab {{ $filter === 'closed' ? 'is-active' : '' }}">Закрытые</a>
                    <a href="{{ route('redmine.appeals', ['project' => $selectedProject->id, 'filter' => 'all']) }}"
                        class="rm-tab {{ $filter === 'all' ? 'is-active' : '' }}">Все</a>
                </nav>
            </div>

            @if($issues->isEmpty())
            <p class="rm-muted">Задач нет.</p>
            @else
            <div class="rm-issue-cards">
                @foreach($issues as $issue)
                @php
                $isClosed = !is_null($issue->closed_on);
                $statusName = $issue->status?->name;
                $isClosedStatus = $isClosed || ($statusName && mb_stripos($statusName, 'закрыт') !== false);
                @endphp
                <a href="{{ route('redmine.issues.show', $issue->id) }}"
                    class="rm-issue-card {{ $isClosedStatus ? 'is-closed' : 'is-open' }}">

                    <div class="rm-issue-card__head">
                        <span class="rm-issue-card__id">#{{ $issue->id }}</span>
                        <span class="rm-issue-card__status">{{ $statusName ?? '—' }}</span>
                    </div>

                    <div class="rm-issue-card__subject">
                        {{ $issue->subject }}
                    </div>

                    <div class="rm-issue-card__meta">
                        <span class="rm-issue-card__tracker">
                            {{ $issue->tracker?->name ?? '—' }}
                        </span>
                        <span class="rm-issue-card__date">
                            {{ $issue->updated_on?->format('d.m.Y H:i') }}
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </section>
        @endif
    </main>
</div>
@endsection
@push('scripts')
<script>
    (function() {
        const form = document.querySelector('.rm-form');
        const trackerSel = form?.querySelector('select[name="tracker_id"]');
        const container = document.getElementById('rm-custom-fields');
        const projectId = form?.querySelector('input[name="project_id"]')?.value;

        if (!form || !trackerSel || !container) return;

        let lastTracker = trackerSel.value;

        trackerSel.addEventListener('change', async () => {
            const trackerId = trackerSel.value;
            if (!trackerId || trackerId === lastTracker) return;
            lastTracker = trackerId;

            container.style.opacity = '.5';
            try {
                const url = new URL('{{ route('redmine.custom-fields') }}', window.location.origin);

                url.searchParams.set('project_id', projectId);
                url.searchParams.set('tracker_id', trackerId);

                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();

                container.innerHTML = renderFields(data.fields || []);
            } catch (e) {
                console.error('custom fields load failed', e);
                container.innerHTML = '<p class="rm-muted">Не удалось загрузить поля.</p>';
            } finally {
                container.style.opacity = '1';
            }
        });

        function esc(s) {
            return String(s ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
        }

        function renderFields(fields) {
            return fields.map(f => {
                const name = `custom_fields[${f.id}]`;
                const req = f.is_required ? 'required' : '';
                const label = `<label class="rm-form__full">${esc(f.name)}${f.is_required ? ' <span class="rm-req">*</span>' : ''}`;
                const val = f.default_value ?? '';
                let input = '';

                switch (f.field_format) {
                    case 'text':
                        input = `<textarea name="${name}" rows="4" ${req}>${esc(val)}</textarea>`;
                        break;
                    case 'int':
                    case 'float':
                        input = `<input type="number" step="${f.field_format === 'float' ? 'any' : '1'}" name="${name}" value="${esc(val)}" ${req}>`;
                        break;
                    case 'date':
                        input = `<input type="date" name="${name}" value="${esc(val)}" ${req}>`;
                        break;
                    case 'bool':
                        input = `<select name="${name}" ${req}><option value="">—</option><option value="1">Да</option><option value="0">Нет</option></select>`;
                        break;
                    case 'list': {
                        const opts = ['<option value="">—</option>']
                            .concat((f.possible_values || []).map(v =>
                                `<option value="${esc(v)}" ${String(val) === String(v) ? 'selected' : ''}>${esc(v)}</option>`))
                            .join('');
                        input = `<select name="${name}" ${req}>${opts}</select>`;
                        break;
                    }
                    default:
                        input = `<input type="text" name="${name}" value="${esc(val)}" ${req}>`;
                }
                return label + input + '</label>';
            }).join('');
        }
    })();
</script>
@endpush
@push('styles')
<style>
    .redmine-appeals {
        display: flex;
        gap: 24px;
        align-items: flex-start;
    }

    .rm-sidebar {
        flex: 0 0 260px;
        border-right: 1px solid #e5e5e5;
        padding-right: 16px;
    }

    .rm-sidebar__title {
        font-size: 16px;
        margin: 0 0 12px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .rm-projects {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .rm-project {
        display: block;
        padding: 8px 10px;
        border-radius: 6px;
        color: #333;
        text-decoration: none;
    }

    .rm-project:hover {
        background: #f3f3f3;
    }

    .rm-project.is-active {
        background: #e8f0fe;
        color: #1a56db;
        font-weight: 600;
    }

    .rm-main {
        flex: 1 1 auto;
        min-width: 0;
    }

    .rm-title {
        margin: 0 0 8px;
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

    .rm-card__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .rm-card__title {
        margin: 0 0 12px;
    }

    .rm-card__head .rm-card__title {
        margin: 0;
    }

    .rm-form {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .rm-form__row {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .rm-form__row>label {
        flex: 1 1 180px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        font-size: 13px;
        color: #555;
    }

    .rm-form__full {
        display: flex;
        flex-direction: column;
        gap: 4px;
        font-size: 13px;
        color: #555;
    }

    .rm-form input,
    .rm-form select,
    .rm-form textarea {
        padding: 6px 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        font-family: inherit;
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

    .rm-tabs {
        display: flex;
        gap: 8px;
    }

    .rm-tab {
        padding: 4px 10px;
        border-radius: 4px;
        text-decoration: none;
        color: #555;
        font-size: 13px;
    }

    .rm-tab.is-active {
        background: #1a56db;
        color: #fff;
    }

    /* ==== КАРТОЧКИ ЗАДАЧ ==== */
    .rm-issue-cards {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .rm-issue-card {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 12px 14px;
        border: 1px solid #e5e5e5;
        border-left: 4px solid #1a56db;
        border-radius: 8px;
        background: #fff;
        color: #333;
        text-decoration: none;
        transition: box-shadow .15s ease, transform .15s ease;
    }

    .rm-issue-card:hover {
        box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
        transform: translateY(-1px);
    }

    .rm-issue-card.is-closed {
        border-left-color: #9aa0a6;
        background: #fafafa;
    }

    .rm-issue-card.is-open {
        border-left-color: #1a56db;
    }

    .rm-issue-card__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
    }

    .rm-issue-card__id {
        color: #888;
        font-weight: 600;
    }

    .rm-issue-card__status {
        padding: 2px 8px;
        border-radius: 999px;
        background: #eef2ff;
        color: #1a56db;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .rm-issue-card.is-closed .rm-issue-card__status {
        background: #eceff1;
        color: #5f6368;
    }

    .rm-issue-card__subject {
        font-size: 14px;
        font-weight: 600;
        line-height: 1.35;
        color: #1f2937;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .rm-issue-card__meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: #777;
        margin-top: auto;
    }

    .rm-issue-card__tracker {
        padding: 2px 8px;
        border-radius: 4px;
        background: #f3f4f6;
        color: #4b5563;
        white-space: nowrap;
    }
</style>
@endpush
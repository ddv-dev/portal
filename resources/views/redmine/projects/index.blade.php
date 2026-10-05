@extends('layouts.app')
@section('title', 'Обращения в подразделения больницы')

@section('content')
<div class="redmine-appeals">

    <aside class="sidebar-1">
        <div class="column-group">

        {{ now() }}
            <span class="section-title-2">Меню</span>

            @if($projects->isEmpty())
            <p class="text-muted">Нет доступных проектов.</p>
            @else
                        <ul class="filter-menu">

            @foreach($projects as $project)
            <li>
                <a href="{{ route('redmine.appeals', ['project' => $project->id, 'filter' => $filter]) }}"
                    class="filter-link rm-project {{ $selectedProject && $selectedProject->id === $project->id ? 'is-active' : '' }}">
                    {{ $project->name }}
                </a>
            </li>
            @endforeach
                        </ul>

            @endif

        </div>


    </aside>



    {{-- ПРАВАЯ КОЛОНКА: форма + история --}}
    <main class="main-content-1">
        @if(!$selectedProject)
        <p class="text-muted">Выберите проект слева.</p>
        @else
        <h1 class="section-title-1">{{ $selectedProject->name }}</h1>
        @if($selectedProject->description)
        <p class="text-muted">{{ \Illuminate\Support\Str::limit($selectedProject->description, 200) }}</p>
        @endif

        {{-- ФОРМА СОЗДАНИЯ ЗАДАЧИ --}}
        <section class="rm-card">

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
                    <label class="text-muted">Трекер
                        <select name="tracker_id" required>
                            @foreach($trackers as $t)
                            <option value="{{ $t->id }}" @selected(old('tracker_id')==$t->id)>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <label class="rm-form__full text-muted">Тема
                    <input type="text" name="subject" required maxlength="255" value="{{ old('subject') }}">
                </label>

                <label class="rm-form__full text-muted">Описание
                    <textarea name="description" rows="6">{{ old('description') }}</textarea>
                </label>

                <label class="rm-form__full text-muted">Вложения
                    <input type="file" name="attachments[]" multiple>
                </label>

                <div id="rm-custom-fields" class="rm-form__custom">
                    @include('redmine.partials.custom-fields', ['customFields' => $customFields])
                </div>

                <div class="rm-form__actions">
                    <button type="submit" class="button button--secondary button--sm">Создать задачу</button>
                </div>
            </form>
        </section>

        {{-- ИСТОРИЯ ЗАДАЧ --}}
        <section class="rm-card">
            <div class="rm-card__head">
                <h3 class="rm-card__title">Мои заявки в проекте</h3>

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
            <p class="text-muted">Задач нет.</p>
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
                const url = new URL('{{route('redmine.custom-fields')}}', window.location.origin);

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
                container.innerHTML = '<p class="text-muted">Не удалось загрузить поля.</p>';
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

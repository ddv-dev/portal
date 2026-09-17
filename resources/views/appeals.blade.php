@extends('layouts.app')
@section('title', 'Обращения в подразделения больницы')


@section('content')
 <h1>Доступные проекты</h1>

    <p class="muted">
        Пользователь: <strong>{{ $user->full_name }}</strong>
        (login: {{ $user->login }}, id: {{ $user->id }})
    </p>

    @if($projects->isEmpty())
        <p>У вас нет доступных проектов с включённым модулем задач.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Идентификатор</th>
                    <th>Родитель</th>
                    <th>Описание</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $project)
                    <tr>
                        <td>{{ $project->id }}</td>
                        <td>{{ $project->name }}</td>
                        <td>{{ $project->identifier }}</td>
                        <td>{{ $project->parent_id ?? '—' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($project->description, 120) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="muted">Всего: {{ $projects->count() }}</p>
    @endif
@endsection

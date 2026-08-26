
@extends('layouts.app')

{{-- Заголовок вкладки --}}
@section('title', 'Главная страница')

{{-- Заголовок в шапке (page_title) --}}
@section('page_title', '🏠 Главная страница')

{{-- Основной контент --}}
@section('content')
    <div class="card">
       
        {{-- Кнопки для демонстрации --}}
        <div style="display: flex; gap: 16px; margin-top: 24px;">
            <button class="button button--primary">Акцентная кнопка</button>
            <button class="button button--secondary">Вторичная кнопка</button>
        </div>
    </div>
@endsection
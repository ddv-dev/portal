@extends('layouts.app')
@push('scripts')
    @vite('resources/js/home.js')
@endpush
{{-- Заголовок вкладки --}}
@section('title', 'Главная страница')

{{-- Основной контент --}}
@section('content')
    <div class="search-box">
        <span class="section-title-2">Поиск</span>
        <form method="GET" action="{{ route('home') }}" id="searchForm">
            <div class="search-input-group">
                <input type="text" name="search" class="search-input" placeholder="Поиск по ФИО, отделу, телефону..."
                    value="{{ request()->input('search') }}" id="searchInput">
                <button class="button button--secondary" type="submit">
                    Найти
                </button>
                @if(request()->input('search') )
                    <div class="reset-wrapper">
                        <a href="{{ route('directory') }}" class="reset-btn" data-reset-all>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
    <!-- Список новостей -->
    @forelse($news as $item)
        <x-news-card :news="$item" :show-attachments="true" :show-read-more="true" :show-meta="true" />
    @empty
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-newspaper text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600">Новостей пока нет</h3>
            <p class="text-gray-400 mt-2">Скоро здесь появятся новые публикации</p>
        </div>
    @endforelse

    <!-- Пагинация с встроенным выбором количества -->
    <div class="mt-6">
        {{ $news->links('vendor.pagination.custom') }}
    </div>
@endsection
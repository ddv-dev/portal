@extends('layouts.app')

{{-- Заголовок вкладки --}}
@section('title', 'Главная страница')

{{-- Основной контент --}}
@section('content')
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
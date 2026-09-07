<?php
// routes/web.php

use App\Http\Controllers\InfoController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\PhoneBookController;
use App\Http\Controllers\BirthdaysController;
use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;
use App\Models\Attachment;
use App\Models\News;
use App\Models\Info;
use App\Models\Sop;
use App\Models\OrderStorage;


// Главная страница
Route::get('/', [NewsController::class, 'index'])->name('home');

// Универсальный маршрут для скачивания вложений
Route::get('/download/{model}/{id}/{attachment}', function ($model, $id, Attachment $attachment) {
    // Проверяем, какая модель используется
    $modelClass = match ($model) {
        'news' => News::class,
        'info' => Info::class,
        'sop' => Sop::class,
        'order' => OrderStorage::class,
        default => abort(404, 'Модель не найдена'),
    };

    // Находим запись
    $record = $modelClass::find($id);
    if (!$record) {
        abort(404, 'Запись не найдена');
    }

    // Проверяем, что вложение принадлежит этой записи
    if (!$record->attachments()->where('attachment_id', $attachment->id)->exists()) {
        abort(404, 'Вложение не принадлежит этой записи');
    }

    $filePath = storage_path('app/public/' . $attachment->path);

    if (!file_exists($filePath)) {
        abort(404, 'Файл не найден');
    }

    return response()->download($filePath, $attachment->name);
})->name('download.attachment');

Route::get('/search', [NewsController::class, 'search'])->name('home.search');

Route::get('/info', [InfoController::class, 'index'])->name('info');

Route::get('/sop', [SopController::class, 'index'])->name('sop');

Route::get('/birthdays', [BirthdaysController::class, 'index'])->name('birthdays');

Route::get('/orders', [OrderController::class, 'index'])->name('orders');

Route::view('/appeals', 'appeals')->name('appeals');

// Телефонный справочник
Route::get('/directory', [PhoneBookController::class, 'index'])->name('directory');

// API для поиска в телефонном справочнике (AJAX)
Route::get('/directory/search', [PhoneBookController::class, 'search'])->name('directory.search');
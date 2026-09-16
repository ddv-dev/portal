<?php

use App\Http\Controllers\InfoController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\PhoneBookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BirthdaysController;
use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;
use App\Models\Attachment;
use App\Models\News;
use App\Models\Info;
use App\Models\Sop;
use App\Models\OrderStorage;

// ===== ПУБЛИЧНЫЕ МАРШРУТЫ (без аутентификации) =====

// Главная страница - доступна всем
Route::get('/', [NewsController::class, 'index'])->name('home');

// Поиск
Route::get('/search', [NewsController::class, 'search'])->name('home.search');

// Информационные страницы
Route::get('/info', [InfoController::class, 'index'])->name('info');
Route::get('/sop', [SopController::class, 'index'])->name('sop');
Route::get('/birthdays', [BirthdaysController::class, 'index'])->name('birthdays');
Route::get('/orders', [OrderController::class, 'index'])->name('orders');
Route::get('/directory', [PhoneBookController::class, 'index'])->name('directory');
Route::get('/directory/search', [PhoneBookController::class, 'search'])->name('directory.search');

// Статические страницы
Route::view('/appeals', 'appeals')->name('appeals');
Route::view('/new_worker', 'new_worker')->name('new_worker');

// Выход - доступен всем
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Универсальный маршрут для скачивания вложений
Route::get('/download/{model}/{id}/{attachment}', function ($model, $id, Attachment $attachment) {
    $modelClass = match ($model) {
        'news' => News::class,
        'info' => Info::class,
        'sop' => Sop::class,
        'order' => OrderStorage::class,
        default => abort(404, 'Модель не найдена'),
    };

    $record = $modelClass::find($id);
    if (!$record) {
        abort(404, 'Запись не найдена');
    }

    if (!$record->attachments()->where('attachment_id', $attachment->id)->exists()) {
        abort(404, 'Вложение не принадлежит этой записи');
    }

    $filePath = storage_path('app/public/' . $attachment->path);

    if (!file_exists($filePath)) {
        abort(404, 'Файл не найден');
    }

    return response()->download($filePath, $attachment->name);
})->name('download.attachment');

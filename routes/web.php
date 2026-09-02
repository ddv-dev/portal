<?php
// routes/web.php

use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PhoneBookController;
use Illuminate\Support\Facades\Route;
use App\Models\Attachment;
use App\Models\News;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Главная страница
Route::get('/', [NewsController::class, 'index'])->name('home');

// Новости - скачивание файлов
Route::get('/news/{news}/download/{attachment}', function (News $news, Attachment $attachment) {
    if (!$news->attachments()->where('attachment_id', $attachment->id)->exists()) {
        abort(404);
    }

    $filePath = storage_path('app/public/' . $attachment->path);

    if (!file_exists($filePath)) {
        abort(404);
    }

    return response()->download($filePath, $attachment->name);
})->name('news.download');

Route::get('/search', [NewsController::class, 'search'])->name('home.search');


// Статические страницы
Route::view('/info', 'info')->name('info');
Route::view('/sop', 'sop')->name('sop');
Route::view('/birthdays', 'birthdays')->name('birthdays');

Route::get('/orders', [OrderController::class, 'index'])->name('orders');


Route::view('/appeals', 'appeals')->name('appeals');

// Телефонный справочник
Route::get('/directory', [PhoneBookController::class, 'index'])->name('directory');

// API для поиска в телефонном справочнике (AJAX)
Route::get('/directory/search', [PhoneBookController::class, 'search'])->name('directory.search');
<?php

use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;
use App\Models\Attachment;
use App\Models\News;
use App\Http\Controllers\PhoneBookController;

Route::get('/', [NewsController::class, 'index'])->name('home');



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

Route::get('/info', function () {
    return view('info');
})->name('info');

Route::get('/sop', function () {
    return view('sop');
})->name('sop');

Route::get('/birthdays', function () {
    return view('birthdays');
})->name('birthdays');

Route::get('/orders', function () {
    return view('orders');
})->name('orders');

Route::get('/appeals', function () {
    return view('appeals');
})->name('appeals');

// Маршрут для телефонного справочника - обновленный
Route::get('/directory', [PhoneBookController::class, 'index'])
    ->name('directory');

// API маршрут для поиска (опционально)
Route::get('/directory/search', [PhoneBookController::class, 'search'])
    ->name('directory.search');
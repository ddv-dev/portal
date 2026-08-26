<?php

use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NewsController::class, 'index'])->name('home');


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

Route::get('/directory', function () {
    return view('directory');
})->name('directory');


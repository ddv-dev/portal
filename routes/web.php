<?php

use App\Http\Controllers\InfoController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\PhoneBookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BirthdaysController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ProfileController;

use Illuminate\Support\Facades\Route;

use App\Models\Attachment;
use App\Models\News;
use App\Models\Info;
use App\Models\Sop;
use App\Models\OrderStorage;

use App\Http\Controllers\Redmine\ProjectController;
use App\Http\Controllers\Redmine\IssueController;
use App\Http\Controllers\Redmine\CustomFieldController;
use App\Http\Controllers\Redmine\AttachmentController as RedmineAttachmentController;

Route::get('/', [NewsController::class, 'index'])->name('home');

Route::get('/search', [NewsController::class, 'search'])->name('home.search');

Route::get('/info', [InfoController::class, 'index'])->name('info');

Route::get('/sop', [SopController::class, 'index'])->name('sop');

Route::get('/birthdays', [BirthdaysController::class, 'index'])->name('birthdays');

Route::get('/orders', [OrderController::class, 'index'])->name('orders');

Route::get('/directory', [PhoneBookController::class, 'index'])->name('directory');

Route::get('/directory/search', [PhoneBookController::class, 'search'])->name('directory.search');


Route::post('/profile/phone', [ProfileController::class, 'updatePhone'])
    ->name('profile.phone.update');

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

Route::prefix('redmine')->name('redmine.')->group(function () {
    // Список проектов + выбранный проект
    Route::get('/appeals',              [ProjectController::class, 'index'])->name('appeals');
    Route::get('/projects/{id}',        [ProjectController::class, 'show'])->name('projects.show');

    // Задачи
    Route::post('/issues',              [IssueController::class, 'store'])->name('issues.store');
    Route::get('/issues/{id}',          [IssueController::class, 'show'])->name('issues.show');
    Route::post('/issues/{id}/comment', [IssueController::class, 'comment'])->name('issues.comment');

    Route::get('/custom-fields', [CustomFieldController::class, 'index'])->name('custom-fields');
    Route::get('/trackers', [CustomFieldController::class, 'trackers'])->name('trackers');

    Route::get('/attachments/{id}/download', [RedmineAttachmentController::class, 'download'])
    ->name('attachments.download');
});

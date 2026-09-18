<?php
// app/Http/Controllers/Redmine/AttachmentController.php

namespace App\Http\Controllers\Redmine;

use App\Http\Controllers\Controller;
use App\Models\Redmine\RedmineAttachment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function download(int $id): StreamedResponse
    {
        $a = RedmineAttachment::findOrFail($id);

        $base = rtrim(config('services.redmine.files_path'), '/');
        $path = $base . '/' . ltrim($a->disk_directory, '/') . '/' . $a->disk_filename;

        if (!is_file($path)) {
            \Log::warning('Attachment file not found', [
                'id'   => $a->id,
                'path' => $path,
            ]);
            abort(404, 'Файл не найден');
        }

        return response()->streamDownload(function () use ($path) {
            readfile($path);
        }, $a->filename, [
            'Content-Type' => $a->content_type ?: 'application/octet-stream',
        ]);
    }
}
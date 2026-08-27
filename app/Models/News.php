<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;

class News extends Model
{
    protected $table = 'new';
    public $timestamps = false;
    
    protected $fillable = [
        'title',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    // Обновленная связь через полиморфную таблицу
    public function attachments()
    {
        return $this->morphToMany(Attachment::class, 'linkable', 'attachment_links');
    }

    // Сохранить новые вложения
    public function saveNewAttachments(array $filePaths)
    {
        if (empty($filePaths)) {
            return [];
        }

        $saved = [];
        foreach ($filePaths as $filePath) {
            if (!Storage::disk('public')->exists($filePath)) {
                continue;
            }

            // Проверяем, не привязан ли уже этот файл
            $exists = $this->attachments()->where('path', $filePath)->exists();

            if ($exists) {
                continue;
            }

            $fullPath = Storage::disk('public')->path($filePath);
            $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
            $filename = basename($filePath);

            // Создаем вложение (без связей)
            $attachment = Attachment::create([
                'name' => $filename,
                'path' => $filePath,
                'type' => $mimeType,
            ]);

            // Привязываем к новости через attachment_links
            $this->attachments()->attach($attachment->id);

            $saved[] = $attachment;
        }

        return $saved;
    }

    // Привязать существующие вложения
    public function attachExistingAttachments(array $attachmentIds)
    {
        if (empty($attachmentIds)) {
            return [];
        }

        $attached = [];
        foreach ($attachmentIds as $attachmentId) {
            $attachment = Attachment::find($attachmentId);
            if ($attachment && !$this->attachments()->where('attachment_id', $attachmentId)->exists()) {
                $this->attachments()->attach($attachmentId);
                $attached[] = $attachment;
            }
        }

        return $attached;
    }

    // Отвязать вложение
    public function detachAttachment($attachmentId)
    {
        $this->attachments()->detach($attachmentId);
    }

    // Получить только изображения
    public function getImagesAttribute()
    {
        return $this->attachments->filter(function ($att) {
            return str_starts_with($att->type, 'image/');
        });
    }

    // Получить только файлы
    public function getFilesAttribute()
    {
        return $this->attachments->filter(function ($att) {
            return !str_starts_with($att->type, 'image/');
        });
    }
}
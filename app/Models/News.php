<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class News extends Model
{
    protected $table = 'new';
    
    // Отключаем автоматическое управление временными метками
    public $timestamps = false;
    
    protected $fillable = [
        'title',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Если нужно использовать created_at вручную
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'new_id');
    }

    // Метод для создания вложений из загруженных файлов
    public function createAttachmentsFromUploads(array $filePaths)
    {
        $created = [];
        foreach ($filePaths as $filePath) {
            $fullPath = storage_path('app/public/' . $filePath);
            
            if (file_exists($fullPath)) {
                $mimeType = mime_content_type($fullPath);
                $filename = basename($filePath);
                
                $attachment = $this->attachments()->create([
                    'name' => $filename,
                    'path' => $filePath,
                    'type' => $mimeType ?: 'application/octet-stream',
                ]);
                
                $created[] = $attachment;
            }
        }
        return $created;
    }
}

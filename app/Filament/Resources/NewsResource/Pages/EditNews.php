<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use App\Models\Attachment;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditNews extends EditRecord
{
    protected static string $resource = NewsResource::class;

    protected function afterSave(): void
    {
        try {
            $record = $this->record;
            $data = $this->data;

            // 1. Сохраняем новые загруженные файлы
            if (isset($data['new_attachments']) && is_array($data['new_attachments'])) {
                $saved = $record->saveNewAttachments($data['new_attachments']);
                Log::info('New attachments saved in edit', [
                    'news_id' => $record->id,
                    'count' => count($saved)
                ]);
            }

            // 2. Привязываем существующие вложения
            if (isset($data['existing_attachments']) && is_array($data['existing_attachments'])) {
                $attached = $record->attachExistingAttachments($data['existing_attachments']);
                Log::info('Existing attachments attached in edit', [
                    'news_id' => $record->id,
                    'count' => count($attached)
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in afterSave attachments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'news_id' => $record->id ?? null
            ]);
        }
    }

    /**
     * Метод для отвязывания вложения
     * Вызывается через Livewire
     */
    public function detachAttachment($attachmentId)
    {
        try {
            $record = $this->record;
            
            // Проверяем, привязано ли вложение к этой новости
            $isAttached = $record->attachments()->where('attachment_id', $attachmentId)->exists();
            
            if ($isAttached) {
                // Отвязываем вложение
                $record->attachments()->detach($attachmentId);
                
                Log::info('Attachment detached from news', [
                    'news_id' => $record->id,
                    'attachment_id' => $attachmentId
                ]);
                
                // Обновляем форму
                $this->form->fill($record->toArray());
            }
        } catch (\Exception $e) {
            Log::error('Error detaching attachment', [
                'error' => $e->getMessage(),
                'attachment_id' => $attachmentId,
                'news_id' => $record->id ?? null
            ]);
        }
    }
}
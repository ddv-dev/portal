<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateNews extends CreateRecord
{
    protected static string $resource = NewsResource::class;

    protected function afterCreate(): void
    {
        try {
            $record = $this->record;
            $data = $this->data;

            // 1. Сохраняем новые загруженные файлы
            if (isset($data['new_attachments']) && is_array($data['new_attachments'])) {
                $saved = $record->saveNewAttachments($data['new_attachments']);
                Log::info('New attachments saved', [
                    'news_id' => $record->id,
                    'count' => count($saved)
                ]);
            }

            // 2. Привязываем существующие вложения
            if (isset($data['existing_attachments']) && is_array($data['existing_attachments'])) {
                $attached = $record->attachExistingAttachments($data['existing_attachments']);
                Log::info('Existing attachments attached', [
                    'news_id' => $record->id,
                    'count' => count($attached)
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in afterCreate attachments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'news_id' => $record->id ?? null
            ]);
        }
    }
}
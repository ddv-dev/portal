<?php
// app/Filament/Resources/OrderStorageResource/Pages/EditOrderStorage.php

namespace App\Filament\Resources\OrderStorageResource\Pages;

use App\Filament\Resources\OrderStorageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOrderStorage extends EditRecord
{
    protected static string $resource = OrderStorageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить'),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Обновляем запись
        $record = parent::handleRecordUpdate($record, $data);
        
        // Обрабатываем вложения
        OrderStorageResource::beforeSave($record, $data);
        
        return $record;
    }

    // Livewire метод для отвязывания вложения
    public function detachAttachment($attachmentId)
    {
        $record = $this->getRecord();
        if ($record) {
            $record->attachments()->detach($attachmentId);
            $this->refreshFormData(['current_attachments']);
        }
    }
}
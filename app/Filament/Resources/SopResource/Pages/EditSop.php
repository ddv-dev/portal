<?php
// app/Filament/Resources/SopResource/Pages/EditSop.php

namespace App\Filament\Resources\SopResource\Pages;

use App\Filament\Resources\SopResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSop extends EditRecord
{
    protected static string $resource = SopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Обновляем запись
        $record = parent::handleRecordUpdate($record, $data);
        
        // Обрабатываем вложения
        SopResource::beforeSave($record, $data);
        
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
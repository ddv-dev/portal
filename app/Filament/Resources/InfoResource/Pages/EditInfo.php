<?php
// app/Filament/Resources/InfoResource/Pages/EditInfo.php

namespace App\Filament\Resources\InfoResource\Pages;

use App\Filament\Resources\InfoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditInfo extends EditRecord
{
    protected static string $resource = InfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record = parent::handleRecordUpdate($record, $data);
        InfoResource::beforeSave($record, $data);
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
<?php
// app/Filament/Resources/InfoResource/Pages/CreateInfo.php

namespace App\Filament\Resources\InfoResource\Pages;

use App\Filament\Resources\InfoResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateInfo extends CreateRecord
{
    protected static string $resource = InfoResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Создаем запись
        $record = parent::handleRecordCreation($data);
        
        // Обрабатываем вложения
        InfoResource::beforeSave($record, $data);
        
        return $record;
    }
}
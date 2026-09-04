<?php
// app/Filament/Resources/OrderStorageResource/Pages/CreateOrderStorage.php

namespace App\Filament\Resources\OrderStorageResource\Pages;

use App\Filament\Resources\OrderStorageResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrderStorage extends CreateRecord
{
    protected static string $resource = OrderStorageResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Создаем запись
        $record = parent::handleRecordCreation($data);
        
        // Обрабатываем вложения
        OrderStorageResource::beforeSave($record, $data);
        
        return $record;
    }
}
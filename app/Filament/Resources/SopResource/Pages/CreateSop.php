<?php
// app/Filament/Resources/SopResource/Pages/CreateSop.php

namespace App\Filament\Resources\SopResource\Pages;

use App\Filament\Resources\SopResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSop extends CreateRecord
{
    protected static string $resource = SopResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Создаем запись
        $record = parent::handleRecordCreation($data);
        
        // Обрабатываем вложения
        SopResource::beforeSave($record, $data);
        
        return $record;
    }
}
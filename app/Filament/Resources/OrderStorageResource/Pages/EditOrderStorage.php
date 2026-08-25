<?php

namespace App\Filament\Resources\OrderStorageResource\Pages;

use App\Filament\Resources\OrderStorageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderStorage extends EditRecord
{
    protected static string $resource = OrderStorageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

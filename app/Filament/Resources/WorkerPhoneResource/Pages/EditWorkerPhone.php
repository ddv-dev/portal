<?php

namespace App\Filament\Resources\WorkerPhoneResource\Pages;

use App\Filament\Resources\WorkerPhoneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkerPhone extends EditRecord
{
    protected static string $resource = WorkerPhoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

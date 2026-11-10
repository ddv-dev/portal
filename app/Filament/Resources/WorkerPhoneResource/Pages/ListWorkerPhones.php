<?php

namespace App\Filament\Resources\WorkerPhoneResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\WorkerPhoneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkerPhones extends ListRecords
{
    protected static string $resource = WorkerPhoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

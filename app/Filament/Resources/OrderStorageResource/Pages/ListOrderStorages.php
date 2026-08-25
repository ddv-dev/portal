<?php

namespace App\Filament\Resources\OrderStorageResource\Pages;

use App\Filament\Resources\OrderStorageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderStorages extends ListRecords
{
    protected static string $resource = OrderStorageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

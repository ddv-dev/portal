<?php

namespace App\Filament\Resources\BuildingResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\BuildingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBuilding extends EditRecord
{
    protected static string $resource = BuildingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

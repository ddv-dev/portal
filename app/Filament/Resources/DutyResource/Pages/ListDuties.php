<?php

namespace App\Filament\Resources\DutyResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\DutyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDuties extends ListRecords
{
    protected static string $resource = DutyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

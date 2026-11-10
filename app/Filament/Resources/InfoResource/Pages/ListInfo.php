<?php

namespace App\Filament\Resources\InfoResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\InfoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInfo
 extends ListRecords
{
    protected static string $resource = InfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

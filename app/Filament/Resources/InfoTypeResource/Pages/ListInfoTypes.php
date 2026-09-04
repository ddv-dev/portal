<?php

namespace App\Filament\Resources\InfoTypeResource\Pages;

use App\Filament\Resources\InfoTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInfoTypes extends ListRecords
{
    protected static string $resource = InfoTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\CorpusDeptResource\Pages;

use App\Filament\Resources\CorpusDeptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCorpusDepts extends ListRecords
{
    protected static string $resource = CorpusDeptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

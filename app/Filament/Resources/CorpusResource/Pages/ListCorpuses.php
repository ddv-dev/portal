<?php

namespace App\Filament\Resources\CorpusResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\CorpusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCorpuses extends ListRecords
{
    protected static string $resource = CorpusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

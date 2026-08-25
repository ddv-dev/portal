<?php

namespace App\Filament\Resources\CorpusResource\Pages;

use App\Filament\Resources\CorpusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCorpus extends EditRecord
{
    protected static string $resource = CorpusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

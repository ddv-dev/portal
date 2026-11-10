<?php

namespace App\Filament\Resources\InfoTypeResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\InfoTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInfoType extends EditRecord
{
    protected static string $resource = InfoTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

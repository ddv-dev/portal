<?php

namespace App\Filament\Resources\DutyResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\DutyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDuty extends EditRecord
{
    protected static string $resource = DutyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

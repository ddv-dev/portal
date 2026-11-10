<?php

namespace App\Filament\Resources\DutyRoleResource\Pages;

use App\Filament\Resources\DutyRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDutyRole extends EditRecord
{
    protected static string $resource = DutyRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

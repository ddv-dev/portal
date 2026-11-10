<?php

namespace App\Filament\Resources\DutyRoleResource\Pages;

use App\Filament\Resources\DutyRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDutyRoles extends ListRecords
{
    protected static string $resource = DutyRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

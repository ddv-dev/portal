<?php

namespace App\Filament\Resources\DeptTypeResource\Pages;

use App\Filament\Resources\DeptTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeptType extends EditRecord
{
    protected static string $resource = DeptTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

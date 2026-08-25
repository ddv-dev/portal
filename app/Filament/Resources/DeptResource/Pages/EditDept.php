<?php

namespace App\Filament\Resources\DeptResource\Pages;

use App\Filament\Resources\DeptResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDept extends EditRecord
{
    protected static string $resource = DeptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

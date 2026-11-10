<?php

namespace App\Filament\Resources\CorpusDeptResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\CorpusDeptResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCorpusDept extends EditRecord
{
    protected static string $resource = CorpusDeptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

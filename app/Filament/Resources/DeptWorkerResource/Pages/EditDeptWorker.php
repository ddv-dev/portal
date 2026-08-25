<?php

namespace App\Filament\Resources\DeptWorkerResource\Pages;

use App\Filament\Resources\DeptWorkerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeptWorker extends EditRecord
{
    protected static string $resource = DeptWorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

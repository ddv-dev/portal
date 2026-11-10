<?php

namespace App\Filament\Resources\DeptWorkerResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\DeptWorkerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeptWorkers extends ListRecords
{
    protected static string $resource = DeptWorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

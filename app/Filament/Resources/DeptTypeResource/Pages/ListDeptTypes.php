<?php

namespace App\Filament\Resources\DeptTypeResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\DeptTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeptTypes extends ListRecords
{
    protected static string $resource = DeptTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

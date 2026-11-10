<?php

namespace App\Filament\Resources\WorkerResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\WorkerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkers extends ListRecords
{
    protected static string $resource = WorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Добавить сотруднкиа')
            ->tooltip('Зарегестрировать нового сотрудника')
            ->icon('heroicon-o-plus'),
        ];
    }
}

<?php

namespace App\Filament\Resources\WorkerResource\Pages;

use App\Filament\Resources\WorkerResource;
use Filament\Actions;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\EditRecord;

class EditWorker extends EditRecord
{
    protected static string $resource = WorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Удалить сотрудника'),
        ];
    }


    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()->label('Сохранить изменения')
                ->icon('heroicon-o-check')
                ->color('success'),
            $this->getCancelFormAction()->label('Отменить')
                ->icon('heroicon-o-x-mark')
                ->color('gray'),
        ];
    }
}

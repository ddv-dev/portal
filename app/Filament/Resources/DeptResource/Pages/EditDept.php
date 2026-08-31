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
            Actions\DeleteAction::make()->label('Удалить отдел'),
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

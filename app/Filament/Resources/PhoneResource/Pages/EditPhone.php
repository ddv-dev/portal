<?php

namespace App\Filament\Resources\PhoneResource\Pages;

use App\Filament\Resources\PhoneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPhone extends EditRecord
{
    protected static string $resource = PhoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Удалить телефон'),
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

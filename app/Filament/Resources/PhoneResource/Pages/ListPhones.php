<?php

namespace App\Filament\Resources\PhoneResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\PhoneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPhones extends ListRecords
{
    protected static string $resource = PhoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Добавить телефон')
                ->tooltip('Зарегестрировать новый телефон')
                ->icon('heroicon-o-plus'),
        ];
    }

}

<?php

namespace App\Filament\Resources\AttachmentResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\AttachmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttachments extends ListRecords
{
    protected static string $resource = AttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

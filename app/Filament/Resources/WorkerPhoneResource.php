<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\WorkerPhoneResource\Pages\ListWorkerPhones;
use App\Filament\Resources\WorkerPhoneResource\Pages\CreateWorkerPhone;
use App\Filament\Resources\WorkerPhoneResource\Pages\EditWorkerPhone;
use App\Filament\Resources\WorkerPhoneResource\Pages;
use App\Filament\Resources\WorkerPhoneResource\RelationManagers;
use App\Models\WorkerPhone;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkerPhoneResource extends Resource
{
    protected static ?string $model = WorkerPhone::class;
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('worker_id')
                    ->required()
                    ->numeric(),
                TextInput::make('phone_id')
                    ->tel()
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('worker_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('phone_id')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkerPhones::route('/'),
            'create' => CreateWorkerPhone::route('/create'),
            'edit' => EditWorkerPhone::route('/{record}/edit'),
        ];
    }
}

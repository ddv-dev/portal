<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkerPhoneResource\Pages;
use App\Filament\Resources\WorkerPhoneResource\RelationManagers;
use App\Models\WorkerPhone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkerPhoneResource extends Resource
{
    protected static ?string $model = WorkerPhone::class;
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('worker_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('phone_id')
                    ->tel()
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('worker_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_id')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListWorkerPhones::route('/'),
            'create' => Pages\CreateWorkerPhone::route('/create'),
            'edit' => Pages\EditWorkerPhone::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DutyRoleResource\Pages;
use App\Filament\Resources\DutyRoleResource\RelationManagers;
use App\Models\DutyRole;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DutyRoleResource extends Resource
{
    protected static ?string $model = DutyRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Название роли')
                ->required()
                ->unique(ignoreRecord: true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Роль'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListDutyRoles::route('/'),
            'create' => Pages\CreateDutyRole::route('/create'),
            'edit' => Pages\EditDutyRole::route('/{record}/edit'),
        ];
    }
}

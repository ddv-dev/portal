<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\DutyRoleResource\Pages\ListDutyRoles;
use App\Filament\Resources\DutyRoleResource\Pages\CreateDutyRole;
use App\Filament\Resources\DutyRoleResource\Pages\EditDutyRole;
use App\Filament\Resources\DutyRoleResource\Pages;
use App\Filament\Resources\DutyRoleResource\RelationManagers;
use App\Models\DutyRole;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DutyRoleResource extends Resource
{
    protected static ?string $model = DutyRole::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Название роли')
                ->required()
                ->unique(ignoreRecord: true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Роль'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
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
            'index' => ListDutyRoles::route('/'),
            'create' => CreateDutyRole::route('/create'),
            'edit' => EditDutyRole::route('/{record}/edit'),
        ];
    }
}

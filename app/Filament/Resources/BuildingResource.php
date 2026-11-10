<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\BuildingResource\Pages\ListBuildings;
use App\Filament\Resources\BuildingResource\Pages\CreateBuilding;
use App\Filament\Resources\BuildingResource\Pages\EditBuilding;
use App\Filament\Resources\BuildingResource\Pages;
use App\Filament\Resources\BuildingResource\RelationManagers;
use App\Models\Building;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static ?string $modelLabel = 'Здание';
    protected static ?string $pluralModelLabel = 'Здания';
    protected static ?string $navigationLabel = 'Здания';
    protected static string | \UnitEnum | null $navigationGroup = 'Управление персоналом';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 2;

    // app/Filament/Resources/BuildingResource.php

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Информация о здании')
                    ->schema([
                        Grid::make(1)->schema([
                            TextInput::make('name')
                                ->label('Название')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('Главный офис'),

                            Textarea::make('address')
                                ->label('Адрес')
                                ->rows(3)
                                ->placeholder('ул. Ленина, д. 1, г. Москва'),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('address')
                    ->label('Адрес')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('corpuses_count')
                    ->label('Корпусов')
                    ->counts('corpuses')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('has_corpuses')
                    ->label('Есть корпуса')
                    ->query(fn($query) => $query->whereHas('corpuses'))
                    ->toggle(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('name');
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
            'index' => ListBuildings::route('/'),
            'create' => CreateBuilding::route('/create'),
            'edit' => EditBuilding::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuildingResource\Pages;
use App\Filament\Resources\BuildingResource\RelationManagers;
use App\Models\Building;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static ?string $modelLabel = 'Здание';
    protected static ?string $pluralModelLabel = 'Здания';
    protected static ?string $navigationLabel = 'Здания';
    protected static ?string $navigationGroup = 'Управление персоналом';
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 2;

    // app/Filament/Resources/BuildingResource.php

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Информация о здании')
                    ->schema([
                        Grid::make(1)->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Название')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('Главный офис'),

                            Forms\Components\Textarea::make('address')
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
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('address')
                    ->label('Адрес')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('corpuses_count')
                    ->label('Корпусов')
                    ->counts('corpuses')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_corpuses')
                    ->label('Есть корпуса')
                    ->query(fn($query) => $query->whereHas('corpuses'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListBuildings::route('/'),
            'create' => Pages\CreateBuilding::route('/create'),
            'edit' => Pages\EditBuilding::route('/{record}/edit'),
        ];
    }
}

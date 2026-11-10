<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\CorpusResource\Pages\ListCorpuses;
use App\Filament\Resources\CorpusResource\Pages\CreateCorpus;
use App\Filament\Resources\CorpusResource\Pages\EditCorpus;
use App\Filament\Resources\CorpusResource\Pages;
use App\Models\Corpus;
use App\Models\Dept;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class CorpusDeptResource extends Resource
{
    protected static ?string $model = Corpus::class;

    protected static ?string $modelLabel = 'Корпус';
    protected static ?string $pluralModelLabel = 'Корпуса';
    protected static ?string $navigationLabel = 'Корпуса';
    protected static string | \UnitEnum | null $navigationGroup = 'Управление персоналом';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Информация о корпусе')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Название корпуса')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('Корпус А'),
                            
                            Select::make('building_id')
                                ->label('Здание')
                                ->relationship('building', 'name')
                                ->required()
                                ->preload()
                                ->searchable()
                                ->placeholder('Выберите здание'),
                        ]),
                    ]),

                Section::make('Отделы в корпусе')
                    ->schema([
                        Repeater::make('corpusDepts')
                            ->label('Отделы')
                            ->relationship('corpusDepts') // ← Используем отношение
                            ->schema([
                                Grid::make(3)->schema([
                                    Select::make('dept_id')
                                        ->label('Отдел')
                                        ->options(Dept::all()->pluck('name_full', 'id'))
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('Выберите отдел'),
                                    
                                    TextInput::make('floor')
                                        ->label('Этаж')
                                        ->numeric()
                                        ->minValue(-10)
                                        ->maxValue(99)
                                        ->placeholder('Номер этажа')
                                        ->helperText('Укажите этаж, где находится отдел'),
                                    
                                    TextInput::make('sector')
                                        ->label('Сектор')
                                        ->maxLength(100)
                                        ->placeholder('Например: A, B, лифт №1')
                                        ->helperText('Ближайший лифт или сектор'),
                                ]),
                            ])
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                Dept::find($state['dept_id'])?->name_full ?? 'Новый отдел'
                            )
                            ->createItemButtonLabel('Добавить отдел')
                            ->columns(1),
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
                
                TextColumn::make('building.name')
                    ->label('Здание')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                
                TextColumn::make('corpusDepts_count')
                    ->label('Отделов')
                    ->counts('corpusDepts')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->toggleable(),
                
                TextColumn::make('corpusDepts')
                    ->label('Отделы с этажами')
                    ->formatStateUsing(function ($record) {
                        return $record->corpusDepts->map(function ($item) {
                            $deptName = $item->dept?->name_full ?? 'Удален';
                            $floor = $item->floor ? "эт. {$item->floor}" : '';
                            $sector = $item->sector ? "сек. {$item->sector}" : '';
                            $parts = array_filter([$deptName, $floor, $sector]);
                            return implode(' | ', $parts);
                        })->implode(', ');
                    })
                    ->limit(50)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('building_id')
                    ->label('Здание')
                    ->relationship('building', 'name')
                    ->preload()
                    ->searchable(),
                
                Filter::make('has_depts')
                    ->label('Есть отделы')
                    ->query(fn ($query) => $query->whereHas('corpusDepts'))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Просмотр'),
                EditAction::make()
                    ->label('Редактировать'),
                DeleteAction::make()
                    ->label('Удалить'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCorpuses::route('/'),
            'create' => CreateCorpus::route('/create'),
            'edit' => EditCorpus::route('/{record}/edit'),
        ];
    }
}
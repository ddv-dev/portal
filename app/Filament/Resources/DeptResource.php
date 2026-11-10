<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\DeptResource\Pages\ListDepts;
use App\Filament\Resources\DeptResource\Pages\CreateDept;
use App\Filament\Resources\DeptResource\Pages\EditDept;
use App\Filament\Resources\DeptResource\Pages;
use App\Models\Dept;
use App\Models\DeptType;
use App\Models\Corpus;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class DeptResource extends Resource
{
    protected static ?string $model = Dept::class;

    protected static ?string $modelLabel = 'Отдел';
    protected static ?string $pluralModelLabel = 'Отделы';
    protected static ?string $navigationLabel = 'Отделы';
    protected static string | \UnitEnum | null $navigationGroup = 'Управление персоналом';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Информация об отделе')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name_full')
                                ->label('Полное название')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('Отдел разработки и сопровождения'),

                            TextInput::make('name_short')
                                ->label('Краткое название')
                                ->maxLength(1000)
                                ->placeholder('ОРиС'),

                            Select::make('type_id')
                                ->label('Тип отдела')
                                ->relationship('type', 'name')
                                ->preload()
                                ->searchable()
                                ->placeholder('Выберите тип отдела'),

                            Textarea::make('description')
                                ->label('Описание')
                                ->maxLength(1000)
                                ->rows(3)
                                ->placeholder('Краткое описание отдела'),
                        ]),
                    ]),

                Section::make('Настройки отображения')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('spr_order')
                                ->label('Порядок в справочнике')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText('Чем меньше число, тем выше позиция в списке. 0 - в конце списка.'),
                            
                            Placeholder::make('spr_order_hint')
                                ->label('')
                                ->content(function ($record) {
                                    if (!$record) return 'При создании нового отдела установите значение';
                                    
                                    $count = Dept::where('spr_order', '<', $record->spr_order)->count();
                                    if ($record->spr_order == 0) {
                                        return '⚠️ Отдел будет отображаться в конце списка';
                                    }
                                    return "📌 Текущая позиция: {$count} место в списке (из " . Dept::count() . " отделов)";
                                })
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Расположение по корпусам')
                    ->schema([
                        Repeater::make('corpusDepts')
                            ->label('Корпуса и местоположение')
                            ->relationship('corpusDepts')
                            ->schema([
                                Grid::make(3)->schema([
                                    Select::make('corpus_id')
                                        ->label('Корпус')
                                        ->options(function () {
                                            return Corpus::with('building')
                                                ->get()
                                                ->mapWithKeys(function ($item) {
                                                    $buildingName = $item->building?->name ?? 'Без здания';
                                                    return [$item->id => "{$item->name} ({$buildingName})"];
                                                })
                                                ->toArray();
                                        })
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('Выберите корпус')
                                        ->helperText('Выберите корпус, где находится отдел'),
                                    
                                    TextInput::make('floor')
                                        ->label('Этаж')
                                        ->numeric()
                                        ->minValue(-10)
                                        ->maxValue(99)
                                        ->step(1)
                                        ->default(null)
                                        ->placeholder('Например: -1, 0, 1, 5')
                                        ->helperText('Отрицательные значения для подземных этажей'),
                                    
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
                                Corpus::find($state['corpus_id'])?->name ?? 'Новый корпус'
                            )
                            ->createItemButtonLabel('Добавить корпус')
                            ->columns(1)
                            ->orderable(false)
                            ->columnSpan('full'),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => !$record || $record->corpusDepts->count() === 0),

                Section::make('Сотрудники')
                    ->schema([
                        Select::make('workers')
                            ->label('Сотрудники')
                            ->relationship('workers', 'name_full')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->placeholder('Добавьте сотрудников'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('spr_order')
                    ->label('Порядок')
                    ->sortable()
                    ->toggleable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => $state == 0 ? 'gray' : 'success'),

                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name_full')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('name_short')
                    ->label('Сокращение')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('type.name')
                    ->label('Тип')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('workers_count')
                    ->label('Сотрудников')
                    ->counts('workers')
                    ->sortable()
                    ->toggleable(),

                // ✅ Улучшенное отображение расположения
                TextColumn::make('corpusDepts')
                    ->label('Расположение')
                    ->formatStateUsing(function ($record) {
                        return $record->corpusDepts->map(function ($item) {
                            $corpusName = $item->corpus?->name ?? '❌ Удален';
                            $buildingName = $item->corpus?->building?->name ?? '';
                            
                            // Форматирование этажа
                            $floor = '';
                            if ($item->floor !== null) {
                                $floorNumber = $item->floor;
                                if ($floorNumber < 0) {
                                    $floor = "подв. эт. {$floorNumber}";
                                } elseif ($floorNumber == 0) {
                                    $floor = "цоколь";
                                } else {
                                    $floor = "эт. {$floorNumber}";
                                }
                            }
                            
                            $sector = $item->sector ? "сек. {$item->sector}" : '';
                            $parts = array_filter([$corpusName, $buildingName, $floor, $sector]);
                            return implode(' | ', $parts);
                        })->implode("\n");
                    })
                    ->html()
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('corpusDepts_count')
                    ->label('Корпусов')
                    ->counts('corpusDepts')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type_id')
                    ->label('Тип отдела')
                    ->relationship('type', 'name')
                    ->preload()
                    ->searchable(),

                // ✅ ИСПРАВЛЕННЫЙ фильтр по корпусу
                SelectFilter::make('corpus')
                    ->label('Корпус')
                    ->options(function () {
                        return Corpus::with('building')
                            ->get()
                            ->mapWithKeys(function ($item) {
                                $buildingName = $item->building?->name ?? 'Без здания';
                                return [$item->id => "{$item->name} ({$buildingName})"];
                            })
                            ->toArray();
                    })
                    ->preload()
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value']) && $data['value'] !== null) {
                            return $query->whereHas('corpusDepts', function (Builder $q) use ($data) {
                                $q->where('corpus_id', $data['value']);
                            });
                        }
                        return $query;
                    }),

                Filter::make('has_corpus')
                    ->label('Есть расположение')
                    ->query(fn (Builder $query): Builder => $query->whereHas('corpusDepts'))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('spr_order', 'asc')
            ->reorderable('spr_order');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDepts::route('/'),
            'create' => CreateDept::route('/create'),
            'edit' => EditDept::route('/{record}/edit'),
        ];
    }
}
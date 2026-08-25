<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeptResource\Pages;
use App\Models\Dept;
use App\Models\DeptType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class DeptResource extends Resource
{
    protected static ?string $model = Dept::class;

    protected static ?string $modelLabel = 'Отдел';
    protected static ?string $pluralModelLabel = 'Отделы';
    protected static ?string $navigationLabel = 'Отделы';
    protected static ?string $navigationGroup = 'Управление персоналом';
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Информация об отделе')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('name_full')
                                ->label('Полное название')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('Отдел разработки и сопровождения'),

                            Forms\Components\TextInput::make('name_short')
                                ->label('Краткое название')
                                ->maxLength(1000)
                                ->placeholder('ОРиС'),

                            Forms\Components\Select::make('type_id')
                                ->label('Тип отдела')
                                ->relationship('type', 'name')
                                ->preload()
                                ->searchable()
                                ->placeholder('Выберите тип отдела'),

                            Forms\Components\Textarea::make('description')
                                ->label('Описание')
                                ->maxLength(1000)
                                ->rows(3)
                                ->placeholder('Краткое описание отдела'),

                        ]),
                    ]),

                Section::make('Сотрудники')
                    ->schema([
                        Forms\Components\Select::make('workers')
                            ->label('Сотрудники')
                            ->relationship('workers', 'name_full')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->placeholder('Добавьте сотрудников'),
                    ]),

                Section::make('Расположение')
                    ->schema([
                        Forms\Components\Select::make('corpuses')
                            ->label('Корпуса')
                            ->relationship('corpuses', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->placeholder('Выберите корпуса'),
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
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name_full')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name_short')
                    ->label('Сокращение')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('type.name')
                    ->label('Тип')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('workers_count')
                    ->label('Сотрудников')
                    ->counts('workers')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('corpuses_count')
                    ->label('Корпусов')
                    ->counts('corpuses')
                    ->sortable()
                    ->toggleable(),
                // В таблице DeptResource
                Tables\Columns\TextColumn::make('corpusDepts')
                    ->label('Расположение')
                    ->formatStateUsing(function ($record) {
                        return $record->corpusDepts->map(function ($item) {
                            $corpusName = $item->corpus?->name ?? 'Удален';
                            $floor = $item->floor ? "эт. {$item->floor}" : '';
                            $sector = $item->sector ? "сек. {$item->sector}" : '';
                            $parts = array_filter([$corpusName, $floor, $sector]);
                            return implode(' | ', $parts);
                        })->implode(', ');
                    })
                    ->limit(50)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type_id')
                    ->label('Тип отдела')
                    ->relationship('type', 'name')
                    ->preload()
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name_full');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepts::route('/'),
            'create' => Pages\CreateDept::route('/create'),
            'edit' => Pages\EditDept::route('/{record}/edit'),
        ];
    }
}
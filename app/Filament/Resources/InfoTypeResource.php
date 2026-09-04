<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InfoTypeResource\Pages;
use App\Models\InfoType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class InfoTypeResource extends Resource
{
    protected static ?string $model = InfoType::class;

    protected static ?string $modelLabel = 'Тип информации';
    protected static ?string $pluralModelLabel = 'Типы информации';
    protected static ?string $navigationLabel = 'Типы информации';
    protected static ?string $navigationGroup = 'Контент';
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Информация о типе')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Название типа')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('Например: Новости, Статьи, Документы...')
                                ->helperText('Введите название типа информации'),

                            Forms\Components\TextInput::make('order_menu')
                                ->label('Порядок в меню')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText('Чем меньше число, тем выше позиция в меню'),
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

                Tables\Columns\TextColumn::make('order_menu')
                    ->label('Порядок')
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color(fn ($state) => $state == 0 ? 'gray' : 'success')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Название скопировано'),

                Tables\Columns\TextColumn::make('infos_count')
                    ->label('Количество записей')
                    ->counts('infos')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_infos')
                    ->label('Есть записи')
                    ->query(fn($query) => $query->whereHas('infos'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать')
                    ->color('primary'),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить')
                    ->color('danger')
                    ->modalHeading('Удаление типа информации')
                    ->modalDescription('Вы уверены, что хотите удалить этот тип информации? Все связанные записи также будут удалены.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->defaultSort('order_menu', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInfoTypes::route('/'),
            'create' => Pages\CreateInfoType::route('/create'),
            'edit' => Pages\EditInfoType::route('/{record}/edit'),
        ];
    }
}
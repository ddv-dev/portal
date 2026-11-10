<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\InfoTypeResource\Pages\ListInfoTypes;
use App\Filament\Resources\InfoTypeResource\Pages\CreateInfoType;
use App\Filament\Resources\InfoTypeResource\Pages\EditInfoType;
use App\Filament\Resources\InfoTypeResource\Pages;
use App\Models\InfoType;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InfoTypeResource extends Resource
{
    protected static ?string $model = InfoType::class;

    protected static ?string $modelLabel = 'Тип информации';
    protected static ?string $pluralModelLabel = 'Типы информации';
    protected static ?string $navigationLabel = 'Типы информации';
    protected static string | \UnitEnum | null $navigationGroup = 'Контент';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Информация о типе')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Название типа')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('Например: Новости, Статьи, Документы...')
                                ->helperText('Введите название типа информации'),

                            TextInput::make('order_menu')
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
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('order_menu')
                    ->label('Порядок')
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color(fn ($state) => $state == 0 ? 'gray' : 'success')
                    ->alignCenter(),

                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Название скопировано'),

                TextColumn::make('infos_count')
                    ->label('Количество записей')
                    ->counts('infos')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('has_infos')
                    ->label('Есть записи')
                    ->query(fn($query) => $query->whereHas('infos'))
                    ->toggle(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Редактировать')
                    ->color('primary'),
                DeleteAction::make()
                    ->label('Удалить')
                    ->color('danger')
                    ->modalHeading('Удаление типа информации')
                    ->modalDescription('Вы уверены, что хотите удалить этот тип информации? Все связанные записи также будут удалены.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->defaultSort('order_menu', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInfoTypes::route('/'),
            'create' => CreateInfoType::route('/create'),
            'edit' => EditInfoType::route('/{record}/edit'),
        ];
    }
}
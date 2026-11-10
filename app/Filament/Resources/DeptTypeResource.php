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
use App\Filament\Resources\DeptTypeResource\Pages\ListDeptTypes;
use App\Filament\Resources\DeptTypeResource\Pages\CreateDeptType;
use App\Filament\Resources\DeptTypeResource\Pages\EditDeptType;
use App\Filament\Resources\DeptTypeResource\Pages;
use App\Models\DeptType;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeptTypeResource extends Resource
{
    protected static ?string $model = DeptType::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';
    protected static string | \UnitEnum | null $navigationGroup = 'Управление персоналом';
    protected static ?string $modelLabel = 'Тип отдела';
    protected static ?string $pluralModelLabel = 'Типы отделов';
    protected static ?string $navigationLabel = 'Типы отделов';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Информация о типе отдела')
                    ->schema([
                        Grid::make(1)->schema([
                            TextInput::make('name')
                                ->label('Название типа')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('Например: Производственный, Административный...')
                                ->helperText('Введите название типа отдела'),
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
                    ->copyable()
                    ->copyMessage('Название скопировано'),

                TextColumn::make('depts_count')
                    ->label('Количество отделов')
                    ->counts('depts')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Обновлен')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('has_depts')
                    ->label('Есть отделы')
                    ->query(fn($query) => $query->whereHas('depts'))
                    ->toggle(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Редактировать')
                    ->color('primary'),
                DeleteAction::make()
                    ->label('Удалить')
                    ->color('danger')
                    ->modalHeading('Удаление типа отдела')
                    ->modalDescription('Вы уверены, что хотите удалить этот тип отдела?'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeptTypes::route('/'),
            'create' => CreateDeptType::route('/create'),
            'edit' => EditDeptType::route('/{record}/edit'),
        ];
    }
}
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeptTypeResource\Pages;
use App\Models\DeptType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class DeptTypeResource extends Resource
{
    protected static ?string $model = DeptType::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Управление персоналом';
    protected static ?string $modelLabel = 'Тип отдела';
    protected static ?string $pluralModelLabel = 'Типы отделов';
    protected static ?string $navigationLabel = 'Типы отделов';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Информация о типе отдела')
                    ->schema([
                        Grid::make(1)->schema([
                            Forms\Components\TextInput::make('name')
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
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Название скопировано'),

                Tables\Columns\TextColumn::make('depts_count')
                    ->label('Количество отделов')
                    ->counts('depts')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлен')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_depts')
                    ->label('Есть отделы')
                    ->query(fn($query) => $query->whereHas('depts'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать')
                    ->color('primary'),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить')
                    ->color('danger')
                    ->modalHeading('Удаление типа отдела')
                    ->modalDescription('Вы уверены, что хотите удалить этот тип отдела?'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeptTypes::route('/'),
            'create' => Pages\CreateDeptType::route('/create'),
            'edit' => Pages\EditDeptType::route('/{record}/edit'),
        ];
    }
}
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderStorageResource\Pages;
use App\Models\OrderStorage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class OrderStorageResource extends Resource
{
    protected static ?string $model = OrderStorage::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Документация';
    protected static ?string $modelLabel = 'Приказ';
    protected static ?string $pluralModelLabel = 'Приказы';
    protected static ?string $navigationLabel = 'Приказы';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Информация о приказе')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Название')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('Введите название приказа...'),
                            
                            Forms\Components\TextInput::make('number')
                                ->label('Номер приказа')
                                ->maxLength(1000)
                                ->placeholder('№ 123-А'),
                            
                            Forms\Components\Toggle::make('is_active')
                                ->label('Активен')
                                ->default(true)
                                ->inline(false),
                            
                            Forms\Components\DateTimePicker::make('created_at')
                                ->label('Дата создания')
                                ->default(now())
                                ->disabled(),
                            
                            Forms\Components\Textarea::make('description')
                                ->label('Описание')
                                ->rows(4)
                                ->placeholder('Краткое описание приказа...'),
                        ]),
                    ]),
                
                Section::make('Вложения')
                    ->schema([
                        Forms\Components\Select::make('attachments')
                            ->label('Вложения')
                            ->relationship('attachments', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->placeholder('Добавьте вложения'),
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
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50)
                    ->copyable()
                    ->copyMessage('Название скопировано'),
                
                Tables\Columns\TextColumn::make('number')
                    ->label('Номер')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(100)
                    ->html()
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_active')
                    ->label('Активные')
                    ->query(fn ($query) => $query->where('is_active', true))
                    ->toggle(),
                
                Tables\Filters\Filter::make('has_description')
                    ->label('С описанием')
                    ->query(fn ($query) => $query->whereNotNull('description'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Просмотр'),
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->searchable()
            ->searchPlaceholder('Поиск приказов...')
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderStorages::route('/'),
            'create' => Pages\CreateOrderStorage::route('/create'),
            'edit' => Pages\EditOrderStorage::route('/{record}/edit'),
        ];
    }
}
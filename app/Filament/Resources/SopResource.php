<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SopResource\Pages;
use App\Models\Sop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class SopResource extends Resource
{
    protected static ?string $model = Sop::class;

    protected static ?string $modelLabel = 'SOP';
    protected static ?string $pluralModelLabel = 'SOP';
    protected static ?string $navigationLabel = 'SOP';
    protected static ?string $navigationGroup = 'Документация';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Информация о SOP')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Название')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('Стандартная операционная процедура...'),

                            Forms\Components\TextInput::make('version')
                                ->label('Версия')
                                ->maxLength(1000)
                                ->placeholder('1.0'),

                            Forms\Components\Textarea::make('description')
                                ->label('Описание')
                                ->rows(4)
                                ->placeholder('Подробное описание процедуры'),

                            Forms\Components\Toggle::make('is_active')
                                ->label('Активен')
                                ->default(true)
                                ->inline(false),
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
                    ->toggleable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),

                Tables\Columns\TextColumn::make('version')
                    ->label('Версия')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('attachments_count')
                    ->label('Вложений')
                    ->counts('attachments')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_active')
                    ->label('Активные')
                    ->query(fn($query) => $query->where('is_active', true))
                    ->toggle(),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSops::route('/'),
            'create' => Pages\CreateSop::route('/create'),
            'edit' => Pages\EditSop::route('/{record}/edit'),
        ];
    }
}
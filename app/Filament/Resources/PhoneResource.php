<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhoneResource\Pages;
use App\Models\Phone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class PhoneResource extends Resource
{
    protected static ?string $model = Phone::class;


    protected static ?string $modelLabel = 'Телефон';
    protected static ?string $pluralModelLabel = 'Телефоны';
    protected static ?string $navigationLabel = 'Телефоны';
    protected static ?string $navigationGroup = 'Управление персоналом';
    protected static ?string $navigationIcon = 'heroicon-o-phone';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Информация о телефоне')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('phone_number')
                                ->label('Номер телефона')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('+7 (495) 123-45-67'),

                            Forms\Components\Toggle::make('is_city_number')
                                ->label('Городской номер')
                                ->default(false)
                                ->inline(false)
                                ->helperText('Отметьте, если это городской номер'),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
           /*     Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),
*/
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Номер телефона')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Номер скопирован'),

                Tables\Columns\IconColumn::make('is_city_number')
                    ->label('Городской')
                    ->boolean()
                    ->trueIcon('heroicon-o-building-office')
                    ->falseIcon('heroicon-o-device-phone-mobile')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('workers_count')
                    ->label('Сотрудников')
                    ->counts('workers')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_city_number')
                    ->label('Городские номера')
                    ->query(fn($query) => $query->where('is_city_number', true))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить')
                ,
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Удалить выбранные'),
                ]),
            ])
            ->defaultSort('phone_number');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhones::route('/'),
            'create' => Pages\CreatePhone::route('/create'),
            'edit' => Pages\EditPhone::route('/{record}/edit'),
        ];
    }
}
<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\PhoneResource\Pages\ListPhones;
use App\Filament\Resources\PhoneResource\Pages\CreatePhone;
use App\Filament\Resources\PhoneResource\Pages\EditPhone;
use App\Filament\Resources\PhoneResource\Pages;
use App\Models\Phone;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PhoneResource extends Resource
{
    protected static ?string $model = Phone::class;


    protected static ?string $modelLabel = 'Телефон';
    protected static ?string $pluralModelLabel = 'Телефоны';
    protected static ?string $navigationLabel = 'Телефоны';
    protected static string | \UnitEnum | null $navigationGroup = 'Управление персоналом';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-phone';
    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Информация о телефоне')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('phone_number')
                                ->label('Номер телефона')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('+7 (495) 123-45-67'),

                            Toggle::make('is_city_number')
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
                TextColumn::make('phone_number')
                    ->label('Номер телефона')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Номер скопирован'),

                IconColumn::make('is_city_number')
                    ->label('Городской')
                    ->boolean()
                    ->trueIcon('heroicon-o-building-office')
                    ->falseIcon('heroicon-o-device-phone-mobile')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->sortable(),

                TextColumn::make('workers_count')
                    ->label('Сотрудников')
                    ->counts('workers')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('is_city_number')
                    ->label('Городские номера')
                    ->query(fn($query) => $query->where('is_city_number', true))
                    ->toggle(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Редактировать'),
                DeleteAction::make()
                    ->label('Удалить'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Удалить выбранные'),
                ]),
            ])
            ->defaultSort('phone_number');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\PhoneResource\RelationManagers\WorkersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPhones::route('/'),
            'create' => CreatePhone::route('/create'),
            'edit' => EditPhone::route('/{record}/edit'),
        ];
    }
}

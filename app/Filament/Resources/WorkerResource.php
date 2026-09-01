<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkerResource\Pages;
use App\Models\Worker;
use App\Models\Dept;
use App\Models\CorpusDept;
use App\Models\Phone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;

class WorkerResource extends Resource
{
    protected static ?string $model = Worker::class;

    protected static ?string $modelLabel = 'Сотрудник';
    protected static ?string $pluralModelLabel = 'Сотрудники';
    protected static ?string $navigationLabel = 'Сотрудники';
    protected static ?string $navigationGroup = 'Управление персоналом';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основная информация')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('name_full')
                                ->label('ФИО')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('Иванов Иван Иванович'),

                            Forms\Components\DatePicker::make('birth_date')
                                ->label('Дата рождения')
                                ->maxDate(now()),

                            Forms\Components\TextInput::make('login_ad')
                                ->label('Логин AD')
                                ->maxLength(1000)
                                ->placeholder('i.ivanov'),

                            Forms\Components\Toggle::make('is_active')
                                ->label('Активен')
                                ->default(true)
                                ->inline(false),
                        ]),
                    ]),

                Section::make('Настройки отображения')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('spr_order')
                                ->label('Порядок в справочнике')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText('Чем меньше число, тем выше позиция в списке.'),

                            Forms\Components\Placeholder::make('spr_order_hint')
                                ->label(' ')
                                ->content('Используется для сортировки сотрудников внутри отдела в телефонном справочнике'),
                        ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Местоположение и должность')
                    ->schema([
                        Repeater::make('deptWorkers')
                            ->label('Отделы и местоположение')
                            ->relationship('deptWorkers')
                            ->schema([
                                Grid::make(2)->schema([
                                    // Основной отдел (для обратной совместимости)
                                    Select::make('dept_id')
                                        ->label('Отдел')
                                        ->relationship('dept', 'name_full')
                                        ->required()
                                        ->preload()
                                        ->searchable()
                                        ->placeholder('Выберите отдел')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            $set('corpus_dept_id', null);
                                        }),

                                    // Конкретное местоположение из corpus_depts
                                    Select::make('corpus_dept_id')
                                        ->label('Корпус / Этаж / Сектор')
                                        ->options(function (callable $get) {
                                            $deptId = $get('dept_id');
                                            if (!$deptId) {
                                                return [];
                                            }

                                            return CorpusDept::with(['corpus', 'dept'])
                                                ->where('dept_id', $deptId)
                                                ->get()
                                                ->mapWithKeys(function ($item) {
                                                    $corpusName = $item->corpus?->name ?? 'Без корпуса';
                                                    $floor = $item->floor !== null ? "эт. {$item->floor}" : '';
                                                    $sector = $item->sector ? "сек. {$item->sector}" : '';
                                                    $location = implode(' | ', array_filter([$corpusName, $floor, $sector]));
                                                    return [$item->id => "{$corpusName} | {$location}"];
                                                })
                                                ->toArray();
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('Сначала выберите отдел')
                                        ->helperText('Выберите конкретное расположение отдела (корпус, этаж, сектор)'),

                                    Forms\Components\TextInput::make('position')
                                        ->label('Должность')
                                        ->maxLength(1000)
                                        ->placeholder('Инженер-программист'),
                                ]),
                            ])
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(
                                fn(array $state): ?string =>
                                Dept::find($state['dept_id'])?->name_full ?? 'Новый отдел'
                            )
                            ->createItemButtonLabel('Добавить отдел')
                            ->columns(1)
                            ->columnSpan('full'),
                    ]),

                Section::make('Телефоны')
                    ->schema([
                        Forms\Components\Select::make('phones')
                            ->label('Телефоны')
                            ->relationship('phones', 'phone_number')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->placeholder('Выберите телефоны')
                            ->helperText('Выберите телефонные номера сотрудника'),
                    ]),

                Section::make('Дополнительная информация')
                    ->schema([
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Создан')
                            ->disabled()
                            ->default(now()),

                        Forms\Components\DateTimePicker::make('updated_at')
                            ->label('Обновлен')
                            ->disabled(),
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
                    ->label('ФИО')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Дата рождения')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(),

                // Добавляем колонку spr_order
                Tables\Columns\TextColumn::make('spr_order')
                    ->label('Порядок')
                    ->sortable()
                    ->toggleable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('deptWorkers')
                    ->label('Отделы и местоположение')
                    ->formatStateUsing(function ($record) {
                        return $record->deptWorkers->map(function ($dw) {
                            $deptName = $dw->dept?->name_short ?? $dw->dept?->name_full ?? 'Удален';

                            // Информация о местоположении
                            $location = '';
                            if ($dw->corpusDept) {
                                $corpus = $dw->corpusDept->corpus?->name ?? '';
                                $floor = $dw->corpusDept->floor !== null ? "эт. {$dw->corpusDept->floor}" : '';
                                $sector = $dw->corpusDept->sector ? "сек. {$dw->corpusDept->sector}" : '';
                                $location = implode(' | ', array_filter([$corpus, $floor, $sector]));
                            }

                            $position = $dw->position ? "({$dw->position})" : '';
                            return trim(implode(' ', array_filter([$deptName, $location, $position])));
                        })->implode("\n");
                    })
                    ->html()
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phones.phone_number')
                    ->label('Телефоны')
                    ->badge()
                    ->color('success')
                    ->separator(', ')
                    ->limitList(3),

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
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('depts')
                    ->label('Отдел')
                    ->relationship('depts', 'name_full')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                Tables\Filters\Filter::make('is_active')
                    ->label('Активные')
                    ->query(fn($query) => $query->where('is_active', true))
                    ->toggle(),

                Tables\Filters\Filter::make('has_phone')
                    ->label('С телефоном')
                    ->query(fn($query) => $query->whereHas('phones'))
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
            // Сортировка по умолчанию - сначала по spr_order, затем по имени
            ->defaultSort('spr_order', 'asc')
            ->defaultSort('name_full', 'asc')
            ->searchable();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkers::route('/'),
            'create' => Pages\CreateWorker::route('/create'),
            'edit' => Pages\EditWorker::route('/{record}/edit'),
        ];
    }
}
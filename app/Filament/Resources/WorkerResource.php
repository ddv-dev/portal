<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\WorkerResource\Pages\ListWorkers;
use App\Filament\Resources\WorkerResource\Pages\CreateWorker;
use App\Filament\Resources\WorkerResource\Pages\EditWorker;
use App\Models\Worker;
use App\Models\Dept;
use App\Models\CorpusDept;
use App\Models\Phone;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class WorkerResource extends Resource
{
    protected static ?string $model = Worker::class;

    protected static ?string $modelLabel = 'Сотрудник';
    protected static ?string $pluralModelLabel = 'Сотрудники';
    protected static ?string $navigationLabel = 'Сотрудники';
    protected static string | \UnitEnum | null $navigationGroup = 'Управление персоналом';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Основная информация')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name_full')
                                ->label('ФИО')
                                ->required()
                                ->maxLength(1000)
                                ->placeholder('Иванов Иван Иванович'),

                            DatePicker::make('birth_date')
                                ->label('Дата рождения')
                                ->maxDate(now()),

                            TextInput::make('login_ad')
                                ->label('Логин AD')
                                ->maxLength(1000)
                                ->placeholder('i.ivanov'),

                            Toggle::make('is_active')
                                ->label('Активен')
                                ->default(true)
                                ->inline(false),

                            TextInput::make('spr_order')
                                ->label('Порядок в справочнике')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText('Чем меньше число, тем выше позиция. Используется для сортировки сотрудников внутри отдела в телефонном справочнике.')
                                ->columnSpanFull(),
                        ]),
                    ]),


                Section::make('Телефоны')
                    ->schema([
                        Select::make('phones')
                            ->label('Телефоны')
                            ->relationship('phones', 'phone_number')
                            ->multiple()
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(
                                fn(Phone $record) => $record->phone_number
                                    . ($record->is_city_number ? ' (городской)' : ' (мобильный)')
                            )
                            ->createOptionForm([
                                TextInput::make('phone_number')
                                    ->label('Номер телефона')
                                    ->required()
                                    ->maxLength(1000)
                                    ->placeholder('+7 (495) 123-45-67'),
                                Toggle::make('is_city_number')
                                    ->label('Городской номер')
                                    ->default(false)
                                    ->helperText('Отметьте, если это городской номер'),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Phone::create($data)->getKey();
                            })
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        $ids = array_filter((array) $value);

                                        if (empty($ids)) {
                                            return;
                                        }
                                        $phones = Phone::query()
                                            ->whereIn('id', $ids)
                                            ->get(['id', 'is_city_number']);

                                        $cityCount   = $phones->where('is_city_number', true)->count();
                                        $mobileCount = $phones->where('is_city_number', false)->count();

                                        if ($cityCount > 1) {
                                            $fail('У сотрудника может быть только один городской номер.');
                                        }

                                        if ($mobileCount > 1) {
                                            $fail('У сотрудника может быть только один внутренний номер.');
                                        }
                                    };
                                },
                            ])
                            ->placeholder('Начните вводить номер телефона')
                            ->helperText('Максимум один городской и один внутренний номер'),
                    ]),


                Section::make('Местоположение и должность')
                    ->schema([
                        Repeater::make('deptWorkers')
                            ->label('Отделы и местоположение')
                            ->relationship('deptWorkers')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('dept_id')
                                        ->label('Отдел')
                                        ->relationship('dept', 'name_full')
                                        ->required()
                                        ->preload()
                                        ->searchable()
                                        ->placeholder('Выберите отдел')
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            $set('corpus_dept_id', null);
                                        }),

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
                                                    return [$item->id => "{$location}"];
                                                })
                                                ->toArray();
                                        })
                                        ->searchable()
                                        ->placeholder('Сначала выберите отдел')
                                        ->helperText('Выберите конкретное расположение отдела (корпус, этаж, сектор)'),

                                    TextInput::make('position')
                                        ->label('Должность')
                                        ->placeholder('Инженер-программист')
                                        ->columnSpanFull(),
                                ]),
                            ])
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(
                                fn(array $state): ?string =>
                                Dept::find($state['dept_id'])?->name_full ?? 'Новый отдел'
                            )
                            ->addActionLabel('Добавить отдел')
                            ->columns(1)
                            ->columnSpan('full'),
                    ]),

                    
                Section::make('Дополнительная информация')
                    ->schema([
                        DateTimePicker::make('created_at')
                            ->label('Создан')
                            ->disabled()
                            ->default(now()),

                        DateTimePicker::make('updated_at')
                            ->label('Обновлен')
                            ->disabled(),
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
                    ->toggleable(),

                TextColumn::make('name_full')
                    ->label('ФИО')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('birth_date')
                    ->label('Дата рождения')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('spr_order')
                    ->label('Порядок')
                    ->sortable()
                    ->toggleable()
                    ->alignCenter(),

                TextColumn::make('deptWorkers')
                    ->label('Отделы и местоположение')
                    ->formatStateUsing(function ($record) {
                        $lines = $record->deptWorkers->map(function ($dw) {
                            $deptName = e($dw->dept?->name_short ?? $dw->dept?->name_full ?? 'Удален');

                            $location = '';
                            if ($dw->corpusDept) {
                                $corpus = e($dw->corpusDept->corpus?->name ?? '');
                                $floor = $dw->corpusDept->floor !== null ? "эт. {$dw->corpusDept->floor}" : '';
                                $sector = $dw->corpusDept->sector ? 'сек. ' . e($dw->corpusDept->sector) : '';
                                $location = implode(' | ', array_filter([$corpus, $floor, $sector]));
                            }

                            $position = $dw->position ? '(' . e($dw->position) . ')' : '';
                            return trim(implode(' ', array_filter([$deptName, $location, $position])));
                        });

                        return new HtmlString($lines->implode('<br>'));
                    })
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('phones.phone_number')
                    ->label('Телефоны')
                    ->badge()
                    ->color('success')
                    ->separator(', ')
                    ->limitList(3),

                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('depts')
                    ->label('Отдел')
                    ->relationship('depts', 'name_full')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                Filter::make('is_active')
                    ->label('Активные')
                    ->query(fn($query) => $query->where('is_active', true))
                    ->toggle(),

                Filter::make('has_phone')
                    ->label('С телефоном')
                    ->query(fn($query) => $query->whereHas('phones'))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Просмотр'),
                EditAction::make()
                    ->label('Редактировать'),
                DeleteAction::make()
                    ->label('Удалить'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query
                ->orderBy('spr_order')
                ->orderBy('name_full')
            );
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkers::route('/'),
            'create' => CreateWorker::route('/create'),
            'edit' => EditWorker::route('/{record}/edit'),
        ];
    }
}
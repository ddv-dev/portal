<?php

namespace App\Filament\Widgets;


use App\Models\DutyWorker;
use App\Models\DutyRole;
use Filament\Forms;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Actions as CalendarActions;
use App\Models\DutyJournal;

class DutyCalendarWidget extends FullCalendarWidget
{
    public string | Model | null $model = DutyWorker::class;

    public function config(): array
    {
        return [
            'selectable' => true,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Права пользователя
    |--------------------------------------------------------------------------
    */

    protected static function currentUser(): ?\App\Models\User
    {
        $user = Auth::user();

        return $user instanceof \App\Models\User ? $user : null;
    }

    protected static function allowedRoleIds(): array
    {
        return static::currentUser()?->manageableDutyRoleIds() ?? [];
    }

    protected static function hasFullAccess(): bool
    {
        return (bool) static::currentUser()?->hasPermission('duty_manage_all');
    }

    /*
    |--------------------------------------------------------------------------
    | Получение доступных ролей
    |--------------------------------------------------------------------------
    */

    protected function getAvailableRoles()
    {
        $query = DutyRole::query()->orderBy('id');

        if (!static::hasFullAccess()) {
            $allowed = static::allowedRoleIds();

            if (empty($allowed)) {
                return collect();
            }

            $query->whereIn('id', $allowed);
        }

        return $query->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Календарь
    |--------------------------------------------------------------------------
    */

    public function fetchEvents(array $fetchInfo): array
    {
        $query = DutyWorker::query()
            ->with('role')
            ->whereBetween(
                'duty_date',
                [$fetchInfo['start'], $fetchInfo['end']]
            );

        if (!static::hasFullAccess()) {
            $allowed = static::allowedRoleIds();

            if (empty($allowed)) {
                return [];
            }

            $query->whereIn('duty_role_id', $allowed);
        }

        return $query->get()->map(fn(DutyWorker $worker) => [
            'id' => $worker->id,
            'title' => $worker->fullname .
                ($worker->role ? ' — ' . $worker->role->name : ''),
            'start' => $worker->duty_date?->toDateString(),
            'allDay' => true,
        ])->all();
    }

    /*
    |--------------------------------------------------------------------------
    | ФОРМА МАССОВОГО ЗАПОЛНЕНИЯ
    |--------------------------------------------------------------------------
    */

    protected function getBulkFormSchema(): array
    {
        $roles = $this->getAvailableRoles();

        $fields = [];

        foreach ($roles as $role) {

            // Количество полей определяется лимитом роли.
            // Если лимит не задан или равен 0, показываем одно поле.
            $limit = max(1, (int) $role->limit_on_day);

            for ($i = 1; $i <= $limit; $i++) {

                $fields[] = Forms\Components\TextInput::make(
                    "assignments.{$role->id}_{$i}"
                )
                    ->label("{$role->name} {$i}")
                    ->placeholder('Введите ФИО')
                    ->maxLength(255)
                    ->columnSpan(1);
            }
        }

        return [
            Forms\Components\DatePicker::make('duty_date')
                ->label('Дата дежурства')
                ->required()
                ->native(false)
                ->displayFormat('d.m.Y'),

            Grid::make()
                ->columns(max(1, 5))
                ->schema($fields),
        ];
    }

    protected function getLastChangeLabel(): string
    {
        $lastChange = DutyJournal::query()
            ->latest('created_at')
            ->first();

        if (!$lastChange) {
            return 'Последнее изменение: нет записей';
        }

        return 'Последнее изменение: ' .
            $lastChange->created_at->format('d.m.Y H:i');
    }


    /*
    |--------------------------------------------------------------------------
    | Заполнение формы существующими дежурствами
    |--------------------------------------------------------------------------
    */

    protected function getExistingAssignments(string $date): array
    {
        $roles = $this->getAvailableRoles();

        $roleIds = $roles->pluck('id')->all();

        if (empty($roleIds)) {
            return [];
        }

        $workers = DutyWorker::query()
            ->whereDate('duty_date', $date)
            ->whereIn('duty_role_id', $roleIds)
            ->get();

        $assignments = [];

        foreach ($workers as $worker) {

            $key = "{$worker->duty_role_id}_{$worker->number}";

            $assignments[$key] = $worker->fullname;
        }

        return $assignments;
    }

    /*
    |--------------------------------------------------------------------------
    | Сохранение всего графика
    |--------------------------------------------------------------------------
    */

    protected function saveDutySchedule(array $data): void
    {
        $date = \Carbon\Carbon::parse(
            $data['duty_date']
        )->toDateString();

        $assignments = $data['assignments'] ?? [];

        $roles = $this->getAvailableRoles();

        DB::transaction(function () use (
            $date,
            $assignments,
            $roles
        ) {

            foreach ($roles as $role) {

                $limit = max(1, (int) $role->limit_on_day);

                for ($number = 1; $number <= $limit; $number++) {

                    $key = "{$role->id}_{$number}";

                    $fullname = trim(
                        (string) ($assignments[$key] ?? '')
                    );

                    $worker = DutyWorker::query()
                        ->whereDate('duty_date', $date)
                        ->where('duty_role_id', $role->id)
                        ->where('number', $number)
                        ->first();

                    // Пустая ячейка:
                    // удаляем существующую запись, если она была.
                    if ($fullname === '') {

                        if ($worker) {
                            $worker->delete();
                        }

                        continue;
                    }

                    // Если сотрудник уже существует,
                    // обновляем ФИО.
                    if ($worker) {

                        if ($worker->fullname !== $fullname) {
                            $worker->fullname = $fullname;
                            $worker->save();
                        }

                        continue;
                    }

                    // Если ячейка свободна,
                    // создаём новую запись.
                    DutyWorker::create([
                        'duty_date' => $date,
                        'fullname' => $fullname,
                        'number' => $number,
                        'duty_role_id' => $role->id,
                    ]);
                }
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Создание новой формы графика
    |--------------------------------------------------------------------------
    */

    protected function makeDutyScheduleAction(): Action
    {
        return CalendarActions\CreateAction::make('createDutySchedule')
            ->label('Заполнить график')
            ->modalHeading('График дежурств')
            ->modalDescription(
                'Заполните ФИО сотрудников в соответствующих столбцах.'
            )
            ->modalWidth('7xl')
            ->schema($this->getBulkFormSchema())

            ->mountUsing(function (
                Schema $schema,
                array $arguments
            ): void {

                $date = $arguments['start'] ?? now()->toDateString();

                $schema->fill([
                    'duty_date' => $date,
                    'assignments' => $this->getExistingAssignments(
                        \Carbon\Carbon::parse($date)->toDateString()
                    ),
                ]);
            })

            ->action(function (array $data): void {

                $this->saveDutySchedule($data);
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Форма создания по клику на дату
    |--------------------------------------------------------------------------
    */

    protected function createAction(): Action
    {
        return $this->makeDutyScheduleAction();
    }

    /*
    |--------------------------------------------------------------------------
    | Кнопка в шапке календаря
    |--------------------------------------------------------------------------
    */


    protected function headerActions(): array
    {
        return [

            // Индикатор последнего изменения
            Action::make('lastChange')
                ->label(fn() => $this->getLastChangeLabel())
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->disabled(),

            // Создание и редактирование графика
            $this->makeDutyScheduleAction(),

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Индивидуальное редактирование существующих записей
    |--------------------------------------------------------------------------
    |
    | Оставляем старую форму для редактирования отдельного сотрудника.
    |
    */

    public function getFormSchema(): array
    {
        $allowedIds = static::allowedRoleIds();

        $fullAccess = static::hasFullAccess();

        $defaultRole = count($allowedIds) === 1
            ? $allowedIds[0]
            : null;

        $recordId = $this->record?->id;

        return [

            Forms\Components\DatePicker::make('duty_date')
                ->label('Дата')
                ->required()
                ->live()
                ->afterStateUpdated(function (
                    $set,
                    $get,
                    ?string $state
                ) use ($recordId) {

                    $roleId = $get('duty_role_id');

                    $nextNumber = DutyWorker::getNextAvailableNumber(
                        $state,
                        $roleId,
                        $recordId
                    );

                    $set('number', (string) $nextNumber);
                }),

            Forms\Components\TextInput::make('fullname')
                ->label('ФИО')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('number')
                ->label('Номер')
                ->numeric()
                ->step(1)
                ->default(1)
                ->required()
                ->maxValue(
                    fn($get) =>
                    DutyRole::find($get('duty_role_id'))?->limit_on_day ?? 99
                )
                ->helperText(
                    fn($get) => ($limit = DutyRole::find(
                        $get('duty_role_id')
                    )?->limit_on_day)
                        ? "Максимум для этой роли: {$limit}"
                        : null
                )
                ->rules([
                    fn($get, $record): \Closure =>
                    function (
                        string $attribute,
                        $value,
                        \Closure $fail
                    ) use ($get, $record) {

                        $date = $get('duty_date');
                        $roleId = $get('duty_role_id');

                        if (!$date || !$roleId || !$value) {
                            return;
                        }

                        $dateString = $date instanceof \Carbon\Carbon
                            ? $date->toDateString()
                            : (string) $date;

                        $query = DutyWorker::whereDate(
                            'duty_date',
                            $dateString
                        )
                            ->where('duty_role_id', $roleId)
                            ->where('number', $value);

                        if ($record) {
                            $query->where('id', '!=', $record->id);
                        }

                        if ($query->exists()) {
                            $fail(
                                "Номер {$value} уже занят на этот день."
                            );
                        }
                    },
                ]),

            Forms\Components\Select::make('duty_role_id')
                ->label('Роль')
                ->options(
                    $this->getAvailableRoles()
                        ->pluck('name', 'id')
                        ->toArray()
                )
                ->default($defaultRole)
                ->disabled(
                    fn() => !$fullAccess && count($allowedIds) === 1
                )
                ->dehydrated()
                ->required()
                ->live()
                ->afterStateUpdated(function (
                    $set,
                    $get,
                    ?string $state
                ) use ($recordId) {

                    $date = $get('duty_date');

                    $nextNumber = DutyWorker::getNextAvailableNumber(
                        $date,
                        $state,
                        $recordId
                    );

                    $set('number', (string) $nextNumber);
                })
                ->visible(
                    fn() => $fullAccess || !empty($allowedIds)
                ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Видимость виджета
    |--------------------------------------------------------------------------
    */

    public static function canView(): bool
    {
        return !request()->routeIs(
            'filament.admin.pages.dashboard'
        );
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\DutyWorker;
use App\Models\DutyRole;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class DutyCalendarWidget extends FullCalendarWidget
{
    public string | \Illuminate\Database\Eloquent\Model | null $model = DutyWorker::class;

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

    public function fetchEvents(array $fetchInfo): array
    {
        $query = DutyWorker::query()
            ->with('role')
            ->whereBetween('duty_date', [$fetchInfo['start'], $fetchInfo['end']]);

        if (!static::hasFullAccess()) {
            $allowed = static::allowedRoleIds();
            if (empty($allowed)) {
                return [];
            }
            $query->whereIn('duty_role_id', $allowed);
        }

        return $query->get()->map(fn(DutyWorker $w) => [
            'id'    => $w->id,
            'title' => $w->fullname . ($w->role ? ' — ' . $w->role->name : ''),
            'start' => $w->duty_date?->toDateString(),
            'allDay' => true,
        ])->all();
    }

    public function getFormSchema(): array
    {
        $allowedIds = static::allowedRoleIds();
        $fullAccess = static::hasFullAccess();
        $defaultRole = count($allowedIds) === 1 ? $allowedIds[0] : null;

        return [
            Forms\Components\DatePicker::make('duty_date')
                ->label('Дата')
                ->required(),

            Forms\Components\TextInput::make('fullname')
                ->label('ФИО')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('number')
                ->label('Номер')
                ->tel()
                ->maxLength(50),

            Forms\Components\Select::make('duty_role_id')
                ->label('Роль')
                ->options(
                    DutyRole::whereIn('id', $allowedIds)->pluck('name', 'id')->toArray()
                )
                ->default($defaultRole)
                ->disabled(fn() => !$fullAccess && count($allowedIds) === 1)
                ->dehydrated()
                ->required()
                ->visible(fn() => $fullAccess || !empty($allowedIds)),
        ];
    }

    public function onEventClick(array $event): void
    {
        // Стандартное поведение: открыть форму редактирования
        parent::onEventClick($event);
    }

    public function onDateSelect(string $start, ?string $end, bool $allDay, ?array $view, ?array $resource): void
    {
        // Ваша проверка прав (если нужно)
        if (!static::hasFullAccess() && empty(static::allowedRoleIds())) {
            return;
        }

        // Рекомендуется вызвать родительский метод для сохранения стандартного поведения
        parent::onDateSelect($start, $end, $allDay, $view, $resource);
    }
}

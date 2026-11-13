<?php

namespace App\Filament\Widgets;

use App\Models\DutyWorker;
use App\Models\DutyRole;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Actions;
use Filament\Schemas\Schema;

class DutyCalendarWidget extends FullCalendarWidget
{
    public string | Model | null $model = DutyWorker::class;

    public function config(): array
    {
        return [
            'selectable' => true,
        ];
    }
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

    /**
     * События календаря: записи duty_worker за видимый диапазон.
     */
    public function fetchEvents(array $fetchInfo): array
    {
        $query = DutyWorker::query()
            ->with('role')
            ->whereBetween('duty_date', [$fetchInfo['start'], $fetchInfo['end']]);

        if (! static::hasFullAccess()) {
            $allowed = static::allowedRoleIds();
            if (empty($allowed)) {
                return [];
            }
            $query->whereIn('duty_role_id', $allowed);
        }

        return $query->get()->map(fn(DutyWorker $w) => [
            'id'     => $w->id,
            'title'  => $w->fullname . ($w->role ? ' — ' . $w->role->name : ''),
            'start'  => $w->duty_date?->toDateString(),
            'allDay' => true,
        ])->all();
    }

    /**
     * Форма и для создания (клик по пустому дню), и для редактирования (клик по событию).
     */
    public function getFormSchema(): array
    {
        $allowedIds  = static::allowedRoleIds();
        $fullAccess  = static::hasFullAccess();
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
                ->disabled(fn() => ! $fullAccess && count($allowedIds) === 1)
                ->dehydrated()
                ->required()
                ->visible(fn() => $fullAccess || ! empty($allowedIds)),
        ];
    }
    public static function canView(): bool
    {
        return ! request()->routeIs('filament.admin.pages.dashboard');
    }

    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mountUsing(function (Schema $schema, array $arguments): void {
                    $schema->fill([
                        'duty_date' => $arguments['start'] ?? null,
                    ]);
                }),
        ];
    }
}

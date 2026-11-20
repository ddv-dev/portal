<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class DutyWorker extends Model
{
    protected $table = 'duty_worker';

    protected $fillable = [
        'duty_date',
        'fullname',
        'number',
        'duty_role_id',
    ];

    protected $casts = [
        'duty_date' => 'date',
    ];


    protected static function booted(): void
    {
        /*
    |--------------------------------------------------------------------------
    | Создание сотрудника
    |--------------------------------------------------------------------------
    */

        static::created(function (DutyWorker $worker) {

            static::writeJournal(
                $worker,
                'created',
                null,
                static::getWorkerSnapshot($worker)
            );
        });

        /*
    |--------------------------------------------------------------------------
    | Изменение сотрудника
    |--------------------------------------------------------------------------
    */

        static::updated(function (DutyWorker $worker) {

            $fields = [
                'fullname',
                'duty_date',
                'duty_role_id',
                'number',
            ];

            $oldValues = [];
            $newValues = [];

            foreach ($fields as $field) {

                if (!$worker->wasChanged($field)) {
                    continue;
                }

                $oldValues[$field] = $worker->getRawOriginal($field);

                $newValues[$field] = $worker->getAttribute($field);
            }

            if (empty($newValues)) {
                return;
            }

            static::writeJournal(
                $worker,
                'updated',
                $oldValues,
                $newValues
            );
        });

        /*
    |--------------------------------------------------------------------------
    | Удаление сотрудника
    |--------------------------------------------------------------------------
    */

        static::deleted(function (DutyWorker $worker) {

            static::writeJournal(
                $worker,
                'deleted',
                static::getWorkerSnapshot($worker),
                null
            );
        });

        /*
    |--------------------------------------------------------------------------
    | Проверка лимита при сохранении
    |--------------------------------------------------------------------------
    */

        static::saving(function (DutyWorker $worker) {

            if (!$worker->duty_role_id || !$worker->duty_date) {
                return;
            }

            $role = DutyRole::find($worker->duty_role_id);

            if (!$role) {
                return;
            }

            $dateString = $worker->duty_date instanceof \Carbon\Carbon
                ? $worker->duty_date->toDateString()
                : (string) $worker->duty_date;

            $ignoreId = $worker->exists ? $worker->id : null;

            if (!$role->hasAvailableSlots($dateString, $ignoreId)) {

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'duty_role_id' =>
                    "Лимит роли «{$role->name}» на этот день исчерпан " .
                        "(максимум: {$role->limit_on_day})."
                ]);
            }
        });
    }

    /*
|--------------------------------------------------------------------------
| Снимок данных сотрудника
|--------------------------------------------------------------------------
*/

    protected static function getWorkerSnapshot(DutyWorker $worker): array
    {
        $role = DutyRole::find($worker->duty_role_id);

        return [
            'fullname' => $worker->fullname,
            'duty_date' => $worker->duty_date?->toDateString()
                ?? $worker->getRawOriginal('duty_date'),
            'duty_role_id' => $worker->duty_role_id,
            'role_name' => $role?->name,
            'number' => $worker->number,
        ];
    }

    /*
|--------------------------------------------------------------------------
| Запись в журнал
|--------------------------------------------------------------------------
*/

    protected static function writeJournal(
        DutyWorker $worker,
        string $action,
        ?array $oldValues,
        ?array $newValues
    ): void {

        $user = \Illuminate\Support\Facades\Auth::user();

        $snapshot = $newValues ?? $oldValues ?? [];

        $roleId = $snapshot['duty_role_id']
            ?? $worker->duty_role_id;

        $role = DutyRole::find($roleId);

        $userName = $user?->name
            ?? $user?->fullname
            ?? $user?->email
            ?? 'Системное действие';

        \App\Models\DutyJournal::create([

            'user_id' => $user?->getAuthIdentifier(),

            'user_name' => $userName,

            'duty_worker_id' => $worker->id,

            'action' => $action,

            'duty_date' => $snapshot['duty_date']
                ?? $worker->getRawOriginal('duty_date'),

            'duty_role_id' => $roleId,

            'role_name' => $snapshot['role_name']
                ?? $role?->name,

            'number' => $snapshot['number']
                ?? $worker->number,

            'old_values' => $oldValues,

            'new_values' => $newValues,

        ]);
    }


    /**
     * Возвращает первый свободный номер дежурства на указанную дату и роль.
     */
    public static function getNextAvailableNumber(?string $date, ?int $roleId, ?int $ignoreId = null): int
    {
        if (!$date || !$roleId) {
            return 1;
        }

        $role = DutyRole::find($roleId);
        if (!$role || empty($role->limit_on_day) || $role->limit_on_day <= 0) {
            return 1; // Если лимита нет, начинаем с 1
        }

        // Получаем все занятые номера на этот день и роль
        $query = static::where('duty_date', $date)
            ->where('duty_role_id', $roleId);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $takenNumbers = $query->pluck('number')
            ->map(fn($n) => (int)$n)
            ->filter()
            ->toArray();

        // Ищем первый свободный номер от 1 до лимита
        for ($i = 1; $i <= $role->limit_on_day; $i++) {
            if (!in_array($i, $takenNumbers)) {
                return $i;
            }
        }

        // Если все заняты, возвращаем 1 (сработает валидация лимита)
        return 1;
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(DutyRole::class, 'duty_role_id');
    }
}

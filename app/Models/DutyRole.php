<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DutyRole extends Model
{
    protected $table = 'duty_role';

    protected $fillable = [
        'name',
        'limit_on_day',
    ];

    protected $casts = [
        'limit_on_day' => 'integer',
    ];

    public $timestamps = false;

    /**
     * Проверяет, есть ли свободные места для этой роли на указанную дату.
     *
     * @param string $date Дата в формате Y-m-d
     * @param int|null $ignoreId ID записи, которую нужно игнорировать (при редактировании)
     * @return bool
     */
    public function hasAvailableSlots(string $date, ?int $ignoreId = null): bool
    {
        // Если лимит не задан или равен 0, считаем, что ограничений нет
        if (empty($this->limit_on_day) || $this->limit_on_day <= 0) {
            return true;
        }

        $query = DutyWorker::query()
            ->where('duty_date', $date)
            ->where('duty_role_id', $this->id);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $currentCount = $query->count();

        return $currentCount < $this->limit_on_day;
    }
}
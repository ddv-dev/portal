<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Worker extends Model
{
    protected $table = 'workers';
    protected $fillable = [
        'name_full',
        'birth_date',
        'login_ad',
        'is_active',
        'spr_order'

    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
        'spr_order' => 'integer'

    ];

    public function deptWorkers()
    {
        return $this->hasMany(DeptWorker::class);
    }

    public function depts()
    {
        return $this->belongsToMany(Dept::class, 'dept_workers')
            ->withPivot('position', 'order_in_dept');
    }

    public function phones()
    {
        return $this->belongsToMany(Phone::class, 'worker_phones');
    }

    public function birthday()
    {
        if (!$this->birth_date) {
            return null;
        }

        $months = [
            1 => 'ЯНВ',
            2 => 'ФЕВ',
            3 => 'МАРТА',
            4 => 'АПР',
            5 => 'МАЯ',
            6 => 'ИЮНЯ',
            7 => 'ИЮЛЯ',
            8 => 'АВГ',
            9 => 'СЕН',
            10 => 'ОКТ',
            11 => 'НОЯБ',
            12 => 'ДЕК',
        ];

        return $this->birth_date->day . ' ' . $months[$this->birth_date->month];
    }

    public function isBirthdayToday()
    {
        if (!$this->birth_date) {
            return false;
        }

        $today = Carbon::now();

        return $this->birth_date->month === $today->month &&
            $this->birth_date->day === $today->day;
    }
}

/*


SELECT * FROM public.workers as w
WHERE w.login_ad is null AND not  (w.birth_date is null)
ORDER BY w.birth_date ASC LIMIT 1000

*/
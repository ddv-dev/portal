<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    protected $fillable = [
        'name_full', 
        'birth_date', 
        'login_ad', 
        'is_active'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function deptWorkers()
    {
        return $this->hasMany(DeptWorker::class);
    }

    public function depts()
    {
        return $this->belongsToMany(Dept::class, 'dept_workers')
                    ->withPivot('position', 'order_in_dept')
                    ->withTimestamps();
    }

    public function phones()
    {
        return $this->belongsToMany(Phone::class, 'worker_phones');
    }
}
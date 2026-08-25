<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeptWorker extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'worker_id', 
        'dept_id', 
        'position', 
        'order_in_dept',
        'corpus_dept_id' // Новое поле
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function dept()
    {
        return $this->belongsTo(Dept::class);
    }
    
    // Новая связь с корпус-отдел
    public function corpusDept()
    {
        return $this->belongsTo(CorpusDept::class);
    }
    
    // Получить локацию в виде строки
    public function getLocationAttribute()
    {
        if ($this->corpusDept) {
            $corpus = $this->corpusDept->corpus?->name ?? '';
            $dept = $this->corpusDept->dept?->name_short ?? $this->dept?->name_short ?? '';
            $floor = $this->corpusDept->floor !== null ? "эт. {$this->corpusDept->floor}" : '';
            $sector = $this->corpusDept->sector ? "сек. {$this->corpusDept->sector}" : '';
            $parts = array_filter([$corpus, $dept, $floor, $sector]);
            return implode(' | ', $parts);
        }
        return $this->dept?->name_short ?? 'Без отдела';
    }
}
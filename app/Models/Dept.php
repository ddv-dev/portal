<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dept extends Model
{
    protected $table = 'dept';
    
    protected $fillable = [
        'name_full',
        'name_short',
        'description',
        'type_id',
        'spr_order'
    ];
    
    public $timestamps = false;
    protected $casts =[
        'spr_order'=>'integer',
    ];
    
    /**
     * Тип отдела
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(DeptType::class, 'type_id');
    }
    
    /**
     * Сотрудники отдела
     */
    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(
            Worker::class,
            'dept_workers',
            'dept_id',
            'worker_id'
        )->withPivot('position', 'order_in_dept');
    }
    
    /**
     * Корпуса, где расположен отдел
     */
    public function corpuses(): BelongsToMany
    {
        return $this->belongsToMany(
            Corpus::class,
            'corpus_depts',
            'dept_id',
            'corpus_id'
        );
    }
    
    /**
     * Записи о сотрудниках в отделе
     */
    public function deptWorkers(): HasMany
    {
        return $this->hasMany(DeptWorker::class, 'dept_id');
    }
    
    /**
     * Связь с корпусами
     */
    public function corpusDepts(): HasMany
    {
        return $this->hasMany(CorpusDept::class, 'dept_id');
    }
}
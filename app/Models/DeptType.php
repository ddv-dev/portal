<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeptType extends Model
{
    protected $table = 'dept_type';
    
    protected $fillable = ['name'];
    
    public $timestamps = false;
    
    /**
     * Отделы этого типа
     */
    public function depts(): HasMany  // ← меняем с departments на depts
    {
        return $this->hasMany(Dept::class, 'type_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Duty extends Model
{
    protected $table = 'duty';

    protected $fillable = ['date', 'updated'];

    protected $casts = [
        'date' => 'date',
        'updated' => 'datetime',
    ];

    public function workers(): HasMany
    {
        return $this->hasMany(DutyWorker::class, 'duty_id');
    }
}
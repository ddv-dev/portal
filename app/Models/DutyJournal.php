<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DutyJournal extends Model
{
    protected $table = 'duty_journal';

    public $timestamps = false; // в таблице нет created_at, только updated_at

    protected $fillable = [
        'duty_worker_id',
        'updated_at',
        'updated_id',
        'col_update',
        'value_old',
        'value_new',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    public function dutyWorker(): BelongsTo
    {
        return $this->belongsTo(DutyWorker::class, 'duty_worker_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_id');
    }
}

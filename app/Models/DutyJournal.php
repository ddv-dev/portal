<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DutyJournal extends Model
{
    protected $table = 'duty_journal';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'duty_worker_id',
        'action',
        'duty_date',
        'duty_role_id',
        'role_name',
        'number',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'duty_date' => 'date',
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\User::class,
            'user_id'
        );
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(
            DutyWorker::class,
            'duty_worker_id'
        );
    }
}

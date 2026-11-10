<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    public function duty(): BelongsTo
    {
        return $this->belongsTo(Duty::class, 'duty_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(DutyRole::class, 'duty_role_id');
    }

    public function journal(): HasMany
    {
        return $this->hasMany(DutyJournal::class, 'duty_worker_id');
    }
}

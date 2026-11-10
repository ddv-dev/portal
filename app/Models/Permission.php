<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends Model
{
    protected $table = 'permission';

    protected $fillable = ['permission', 'description', 'duty_role_id'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_permissions',
            'permission_id',
            'user_id'
        );
    }

    public function dutyRole(): BelongsTo
    {
        return $this->belongsTo(DutyRole::class, 'duty_role_id');
    }
}

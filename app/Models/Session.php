<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    protected $table = 'sessions';
    
    protected $fillable = [
        'id',
        'user_id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity'
    ];
    
    public $timestamps = false;
    
    protected $keyType = 'string';
    public $incrementing = false;
    
    /**
     * Пользователь
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
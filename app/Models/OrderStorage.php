<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderStorage extends Model
{
    protected $table = 'order_storage';
    
    protected $fillable = [
        'title',
        'description',
        'number',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];
    
    public $timestamps = false;
    
    /**
     * Вложения
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'order_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sop extends Model
{
    protected $table = 'sop';
    
    protected $fillable = [
        'title',
        'description',
        'version',
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
        return $this->hasMany(Attachment::class, 'sop_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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
     * Вложения (полиморфная связь через attachment_links)
     */
    public function attachments(): MorphToMany
    {
        return $this->morphToMany(
            Attachment::class, 
            'linkable', 
            'attachment_links'
        );
    }
}
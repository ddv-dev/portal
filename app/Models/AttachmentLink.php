<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttachmentLink extends Model
{
    protected $table = 'attachment_links';
    
    protected $fillable = [
        'attachment_id',
        'linkable_type',
        'linkable_id',
    ];

    public function attachment()
    {
        return $this->belongsTo(Attachment::class);
    }

    // Полиморфная связь
    public function linkable()
    {
        return $this->morphTo();
    }
}
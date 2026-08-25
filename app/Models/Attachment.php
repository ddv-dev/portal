<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    protected $table = 'attachments';
    
    protected $fillable = [
        'type',
        'path',
        'name',
        'sop_id',
        'order_id',
        'new_id'
    ];
    
    public $timestamps = false;
    
    /**
     * Связь с SOP
     */
    public function sop(): BelongsTo
    {
        return $this->belongsTo(Sop::class, 'sop_id');
    }
    
    /**
     * Связь с заказом
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderStorage::class, 'order_id');
    }
    
    /**
     * Связь с новостью
     */
    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class, 'new_id');
    }
}
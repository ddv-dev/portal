<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerPhone extends Model
{
    protected $table = 'worker_phones';
    
    protected $fillable = [
        'worker_id',
        'phone_id'
    ];
    
    public $timestamps = false;
    
    /**
     * Сотрудник
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class, 'worker_id');
    }
    
    /**
     * Телефон
     */
    public function phone(): BelongsTo
    {
        return $this->belongsTo(Phone::class, 'phone_id');
    }
}
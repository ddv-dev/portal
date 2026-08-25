<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Phone extends Model
{
    protected $table = 'phone';
    
    protected $fillable = [
        'phone_number',
        'is_city_number'
    ];
    
    protected $casts = [
        'is_city_number' => 'boolean',
    ];
    
    public $timestamps = false;
    
    /**
     * Сотрудники с этим телефоном
     */
    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(
            Worker::class,
            'worker_phones',
            'phone_id',
            'worker_id'
        );
    }
}
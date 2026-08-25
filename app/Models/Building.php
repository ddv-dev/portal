<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    protected $table = 'building';
    
    protected $fillable = [
        'name',
        'address'
    ];
    
    public $timestamps = false;
    
    /**
     * Корпуса в здании
     */
    public function corpuses(): HasMany
    {
        return $this->hasMany(Corpus::class, 'building_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class InfoType extends Model
{
    protected $table = 'info_type';

    protected $fillable = [
        'name',
        'order_menu'
    ];

    protected $casts = [
        'order_menu' => 'integer'
    ];

    public $timestamps = false;

    /**
     * Все записи info этого типа
     */
    public function infos(): HasMany
    {
        return $this->hasMany(Info::class, 'type_id', 'id');
    }

    /**
     * Scope для сортировки по полю order_menu
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order_menu', 'asc');
    }
}
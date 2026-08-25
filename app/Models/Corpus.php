<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Corpus extends Model
{
    protected $table = 'corpus';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'building_id'
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    // Старое отношение (оставляем для совместимости)
    public function depts(): BelongsToMany
    {
        return $this->belongsToMany(Dept::class, 'corpus_depts');
    }

    // Новое отношение для работы с этажами и секторами
    public function corpusDepts(): HasMany
    {
        return $this->hasMany(CorpusDept::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorpusDept extends Model
{
    protected $table = 'corpus_depts';
    public $timestamps = false;

    protected $fillable = [
        'corpus_id',
        'dept_id',
        'floor',
        'sector'
    ];

    protected $casts = [
        'floor' => 'integer',
    ];

    public function corpus(): BelongsTo
    {
        return $this->belongsTo(Corpus::class);
    }

    public function dept(): BelongsTo
    {
        return $this->belongsTo(Dept::class);
    }
}
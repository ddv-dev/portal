<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class Notification extends Model
{
    protected $table = 'notifications';
    public $timestamps = false;

    protected $fillable = [
        'worker_id', 'actor_worker_id', 'type',
        'notifiable_type', 'notifiable_id',
        'title', 'body', 'payload', 'url',
        'read_at', 'created_at', 'dedup_key',
    ];

    protected $casts = [
        'payload'    => 'array',
        'read_at'    => 'datetime',
        'created_at' => 'datetime',
    ];

    public function worker(): BelongsTo      { return $this->belongsTo(Worker::class, 'worker_id'); }
    public function actor(): BelongsTo       { return $this->belongsTo(Worker::class, 'actor_worker_id'); }
    public function notifiable(): MorphTo    { return $this->morphTo(); }

    public function scopeForWorker(Builder $q, int $workerId): Builder
    {
        return $q->where('worker_id', $workerId);
    }

    public function scopeUnread(Builder $q): Builder
    {
        return $q->whereNull('read_at');
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }
}
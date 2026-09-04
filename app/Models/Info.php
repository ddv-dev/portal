<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Models\InfoType;

class Info extends Model
{
    protected $table = 'info';
    protected $fillable = [
        'title',
        'created_at',
        'order',
        'type_id'

    ];

    protected $casts = [
        'crated_at' => 'datatime',
        'order' => 'integer',
        'type_id' => 'integer'
    ];

    public $timestamps = false;

    public function type(): BelongsTo
    {
        return $this->belongsTo(InfoType::class, 'type_id', 'id');
    }
   /**
     * Вложения (полиморфная связь через attachment_links)
     */
        public function attachments(): MorphToMany
    {
        return $this->morphToMany(
            Attachment::class,
            'linkable',
            'attachment_links',
            'linkable_id',
            'attachment_id',
            'id',
            'id'
        );
    }

    /**
     * Связи с вложениями (прямой доступ к attachment_links)
     */
    public function attachmentLinks()
    {
        return $this->morphMany(AttachmentLink::class, 'linkable');
    }

    /**
     * Scope для выборки по типу информации
     */
    public function scopeByType(Builder $query, int $typeId): Builder
    {
        return $query->where('type_id', $typeId);
    }

    /**
     * Scope для сортировки по полю order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order', 'asc');
    }
}

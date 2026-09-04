<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Builder;

class Sop extends Model
{
    protected $table = 'sop';

    protected $fillable = [
        'title',
        'description',
        'version',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }
}
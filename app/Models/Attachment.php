<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Attachment extends Model
{
    protected $table = 'attachments';
    public $timestamps = false;
    
    protected $fillable = [
        'type',
        'path',
        'name',
    ];

    // Полиморфная связь с моделями через attachment_links
    public function news()
    {
        return $this->morphedByMany(News::class, 'linkable', 'attachment_links');
    }

    public function sop()
    {
        return $this->morphedByMany(Sop::class, 'linkable', 'attachment_links');
    }

    public function orders()
    {
        return $this->morphedByMany(OrderStorage::class, 'linkable', 'attachment_links');
    }

    // Получить все связи
    public function links()
    {
        return $this->hasMany(AttachmentLink::class);
    }

    // Проверить, привязано ли вложение к чему-либо
    public function isUsed()
    {
        return $this->links()->exists();
    }

    // Получить тип сущности, к которой привязано вложение
    public function getLinkedEntityType()
    {
        $link = $this->links()->first();
        return $link ? $link->linkable_type : null;
    }

    // Получить ID сущности, к которой привязано вложение
    public function getLinkedEntityId()
    {
        $link = $this->links()->first();
        return $link ? $link->linkable_id : null;
    }
}
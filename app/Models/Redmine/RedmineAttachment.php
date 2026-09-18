<?php
// app/Models/Redmine/RedmineAttachment.php

namespace App\Models\Redmine;

class RedmineAttachment extends RedmineModel
{
    protected $table = 'attachments';

    const CREATED_AT = 'created_on';
    const UPDATED_AT = null;

    protected $casts = [
        'created_on' => 'datetime',
        'filesize'   => 'integer',
    ];

    public function author()
    {
        return $this->belongsTo(RedmineUser::class, 'author_id');
    }

    public function container()
    {
        return $this->morphTo();
    }
}
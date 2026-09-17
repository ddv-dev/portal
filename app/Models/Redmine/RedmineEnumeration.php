<?php
// app/Models/Redmine/RedmineEnumeration.php

namespace App\Models\Redmine;

class RedmineEnumeration extends RedmineModel
{
    protected $table = 'enumerations';

    protected $casts = [
        'is_default' => 'boolean',
        'active'     => 'boolean',
        'position'   => 'integer',
    ];

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}
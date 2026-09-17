<?php

namespace App\Models\Redmine;

class RedmineRole extends RedmineModel
{
    protected $table = 'roles';

    protected $casts = [
        'assignable' => 'boolean',
        'builtin'    => 'integer',
        'position'   => 'integer',
    ];
}
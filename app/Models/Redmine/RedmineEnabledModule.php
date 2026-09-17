<?php

namespace App\Models\Redmine;

class RedmineEnabledModule extends RedmineModel
{
    protected $table = 'enabled_modules';

    public function project()
    {
        return $this->belongsTo(RedmineProject::class, 'project_id');
    }
}
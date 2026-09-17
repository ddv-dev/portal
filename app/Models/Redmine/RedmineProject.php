<?php
// app/Models/Redmine/RedmineProject.php

namespace App\Models\Redmine;

class RedmineProject extends RedmineModel
{
    protected $table = 'projects';

    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'updated_on';

    protected $casts = [
        'is_public'      => 'boolean',
        'status'         => 'integer',
        'inherit_members'=> 'boolean',
        'created_on'     => 'datetime',
        'updated_on'     => 'datetime',
    ];

    // ----- Связи -----

    public function issues()
    {
        return $this->hasMany(RedmineIssue::class, 'project_id');
    }

    public function members()
    {
        return $this->hasMany(RedmineMember::class, 'project_id');
    }

    public function users()
    {
        return $this->belongsToMany(
            RedmineUser::class,
            'members',
            'project_id',
            'user_id'
        );
    }

    public function enabledModules()
    {
        return $this->hasMany(RedmineEnabledModule::class, 'project_id');
    }

    public function parent()
    {
        return $this->belongsTo(RedmineProject::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(RedmineProject::class, 'parent_id');
    }

    // ----- Хелперы -----

    /**
     * Активен ли проект (status = 1 в Redmine).
     */
    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    /**
     * Включён ли модуль отслеживания задач.
     */
    public function hasIssueTracking(): bool
    {
        return $this->enabledModules()
            ->where('name', 'issue_tracking')
            ->exists();
    }
}
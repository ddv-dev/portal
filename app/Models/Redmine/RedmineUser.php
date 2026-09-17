<?php
// app/Models/Redmine/RedmineUser.php

namespace App\Models\Redmine;

class RedmineUser extends RedmineModel
{
    protected $table = 'users';

    const CREATED_AT = 'created_on';
    const UPDATED_AT = null; // в users нет updated_on

    protected $casts = [
        'admin'         => 'boolean',
        'status'        => 'integer',
        'created_on'    => 'datetime',
        'updated_on'    => 'datetime',
        'last_login_on' => 'datetime',
    ];

    // ----- Связи -----

    public function authoredIssues()
    {
        return $this->hasMany(RedmineIssue::class, 'author_id');
    }

    public function assignedIssues()
    {
        return $this->hasMany(RedmineIssue::class, 'assigned_to_id');
    }

    public function journals()
    {
        return $this->hasMany(RedmineJournal::class, 'user_id');
    }

    public function members()
    {
        return $this->hasMany(RedmineMember::class, 'user_id');
    }

    public function projects()
    {
        return $this->belongsToMany(
            RedmineProject::class,
            'members',
            'user_id',
            'project_id'
        );
    }

    // ----- Хелперы -----

    /**
     * Полное имя (для отображения).
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->firstname ?? '') . ' ' . ($this->lastname ?? ''));
    }

    /**
     * Найти пользователя по логину.
     */
    public static function findByLogin(string $login): ?self
    {
        return static::where('login', $login)->first();
    }
}

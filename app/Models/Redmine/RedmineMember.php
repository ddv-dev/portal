<?php
// app/Models/Redmine/RedmineMember.php

namespace App\Models\Redmine;

class RedmineMember extends RedmineModel
{
    protected $table = 'members';

    const CREATED_AT = 'created_on';
    const UPDATED_AT = null;

    protected $casts = [
        'created_on'       => 'datetime',
        'mail_notification'=> 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(RedmineUser::class, 'user_id');
    }

    public function project()
    {
        return $this->belongsTo(RedmineProject::class, 'project_id');
    }

    public function memberRoles()
    {
        return $this->hasMany(RedmineMemberRole::class, 'member_id');
    }

    public function roles()
    {
        return $this->belongsToMany(
            RedmineRole::class,
            'member_roles',
            'member_id',
            'role_id'
        );
    }
}
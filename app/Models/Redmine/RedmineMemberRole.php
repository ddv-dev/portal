<?php

namespace App\Models\Redmine;

class RedmineMemberRole extends RedmineModel
{
    protected $table = 'member_roles';

    public function member()
    {
        return $this->belongsTo(RedmineMember::class, 'member_id');
    }

    public function role()
    {
        return $this->belongsTo(RedmineRole::class, 'role_id');
    }

    public function inheritedFrom()
    {
        return $this->belongsTo(RedmineMember::class, 'inherited_from');
    }
}
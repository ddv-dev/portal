<?php
// app/Models/Redmine/RedmineIssueStatus.php

namespace App\Models\Redmine;

class RedmineIssueStatus extends RedmineModel
{
    protected $table = 'issue_statuses';

    protected $casts = [
        'is_closed'          => 'boolean',
        'position'           => 'integer',
        'default_done_ratio' => 'integer',
    ];

    public function issues()
    {
        return $this->hasMany(RedmineIssue::class, 'status_id');
    }
}
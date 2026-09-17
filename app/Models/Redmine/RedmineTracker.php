<?php
// app/Models/Redmine/RedmineTracker.php

namespace App\Models\Redmine;

class RedmineTracker extends RedmineModel
{
    protected $table = 'trackers';

    public function issues()
    {
        return $this->hasMany(RedmineIssue::class, 'tracker_id');
    }
}
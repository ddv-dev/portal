<?php

namespace App\Models\Redmine;

class RedmineIssue extends RedmineModel
{
    protected $table = 'issues';

    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'updated_on';

    protected $casts = [
        'is_private'      => 'boolean',
        'done_ratio'      => 'integer',
        'estimated_hours' => 'float',
        'created_on'      => 'datetime',
        'updated_on'      => 'datetime',
        'closed_on'       => 'datetime',
        'start_date'      => 'date',
        'due_date'        => 'date',
    ];

    // ----- Связи -----

    public function project()
    {
        return $this->belongsTo(RedmineProject::class, 'project_id');
    }

    public function author()
    {
        return $this->belongsTo(RedmineUser::class, 'author_id');
    }

    public function assignee()
    {
        return $this->belongsTo(RedmineUser::class, 'assigned_to_id');
    }

    public function status()
    {
        return $this->belongsTo(RedmineIssueStatus::class, 'status_id');
    }

    public function tracker()
    {
        return $this->belongsTo(RedmineTracker::class, 'tracker_id');
    }

    public function priority()
    {
        return $this->belongsTo(RedmineEnumeration::class, 'priority_id')
            ->where('type', 'IssuePriority');
    }

    public function journals()
    {
        return $this->hasMany(RedmineJournal::class, 'journalized_id')
            ->where('journalized_type', 'Issue')
            ->orderBy('created_on');
    }

    public function parent()
    {
        return $this->belongsTo(RedmineIssue::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(RedmineIssue::class, 'parent_id');
    }


    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('author_id', $userId)
                ->orWhere('assigned_to_id', $userId);
        });
    }

    public function scopeOpen($query)
    {
        return $query->whereNull('closed_on');
    }

    public function scopeInProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}

<?php
// app/Models/Redmine/RedmineJournal.php

namespace App\Models\Redmine;

class RedmineJournal extends RedmineModel
{
    protected $table = 'journals';

    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'updated_on';

    protected $casts = [
        'private_notes' => 'boolean',
        'created_on'    => 'datetime',
        'updated_on'    => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(RedmineUser::class, 'user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(RedmineUser::class, 'updated_by_id');
    }

    public function details()
    {
        return $this->hasMany(RedmineJournalDetail::class, 'journal_id');
    }

    public function issue()
    {
        return $this->belongsTo(RedmineIssue::class, 'journalized_id')
                    ->where('journalized_type', 'Issue');
    }
}
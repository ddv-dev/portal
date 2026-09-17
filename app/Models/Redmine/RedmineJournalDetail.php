<?php
// app/Models/Redmine/RedmineJournalDetail.php

namespace App\Models\Redmine;

class RedmineJournalDetail extends RedmineModel
{
    protected $table = 'journal_details';

    public function journal()
    {
        return $this->belongsTo(RedmineJournal::class, 'journal_id');
    }
}
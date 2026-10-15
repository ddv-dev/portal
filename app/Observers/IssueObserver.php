<?php

use App\Models\Redmine\RedmineIssue;

use App\Notifications\TicketStatusChanged;
use Illuminate\Support\Facades\Notification;

class IssueObserver{
    public function updated(RedmineIssue $issue) {
        if ($issue->isDirty('status')){
            
        }
        
    }
}
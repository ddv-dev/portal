<?php

namespace App\Http\Controllers\Redmine;

use App\Http\Controllers\Controller;
use App\Models\Redmine\RedmineUser;
use App\Services\Redmine\RedmineProjectService;

class ProjectController extends Controller
{
    public function __construct(
        protected RedmineProjectService $projects,
    ) {}

    public function index()
    {
        $userId = session('redmine_user_id');

        if (!$userId) {
            return view('redmine.no-user');
        }

        $user = RedmineUser::find($userId);
        if (!$user) {
            return view('redmine.no-user');
        }

        $projects = $this->projects->getAvailableForUser($user);

        return view('appeals', compact('projects', 'user'));
    }
}

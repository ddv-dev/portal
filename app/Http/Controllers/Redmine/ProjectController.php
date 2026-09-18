<?php

namespace App\Http\Controllers\Redmine;

use App\Http\Controllers\Controller;
use App\Models\Redmine\RedmineUser;
use App\Services\Redmine\RedmineProjectService;
use App\Services\Redmine\RedmineIssueService;
use App\Services\Redmine\RedmineCustomFieldService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        protected RedmineProjectService     $projects,
        protected RedmineIssueService       $issues,
        protected RedmineCustomFieldService $customFields,
    ) {}

    /**
     * Список проектов + (если выбран) форма создания задачи и история.
     */
    public function index(Request $request)
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

        // Выбранный проект (из query string ?project=ID)
        $selectedProject = null;
        $selectedId = (int) $request->query('project');
        if ($selectedId) {
            $selectedProject = $projects->firstWhere('id', $selectedId);
        }
        // Если ничего не выбрано — берём первый
        if (!$selectedProject && $projects->isNotEmpty()) {
            $selectedProject = $projects->first();
        }

        // Значения по умолчанию — чтобы переменные были во вьюхе всегда
        $trackers     = collect();
        $issues       = collect();
        $customFields = collect();
        $filter       = $request->query('filter', 'open'); // open|closed|all

        if ($selectedProject) {
            $dict       = $this->issues->getFormDictionaries();
            $trackers = $this->issues->getTrackersForProject($selectedProject->id);

            // Кастомные поля для пары проект + выбранный (или первый) трекер
            $trackerId = (int) old('tracker_id', $trackers->first()->id ?? 0);
            $customFields = $trackerId
                ? $this->customFields->forProjectAndTracker($selectedProject->id, $trackerId)
                : collect();

            $issues = $this->issues->getUserIssuesInProject(
                $user->id,
                $selectedProject->id,
                $filter
            );
        }

        return view('redmine.projects.index', compact(
            'user',
            'projects',
            'selectedProject',
            'trackers',
            'issues',
            'customFields',
            'filter'
        ));
    }

    /**
     * Отдельный роут /projects/{id} — редирект на index с ?project=ID.
     */
    public function show(int $id)
    {
        return redirect()->route('redmine.appeals', ['project' => $id]);
    }
}
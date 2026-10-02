<?php
// app/Http/Controllers/Redmine/IssueController.php

namespace App\Http\Controllers\Redmine;

use App\Http\Controllers\Controller;
use App\Models\Redmine\RedmineUser;
use App\Models\Redmine\RedmineIssue;
use App\Services\Redmine\RedmineIssueService;
use Illuminate\Http\Request;
use App\Services\Redmine\RedmineCustomFieldService;
use App\Services\Redmine\RedmineAttachmentService;

class IssueController extends Controller
{
    public function __construct(
        protected RedmineIssueService $issues,
        protected RedmineCustomFieldService $customFields,
    ) {}

    public function store(Request $request,  RedmineAttachmentService $attachments)
    {
        $user = RedmineUser::find(session('redmine_user_id'));
        if (!$user) return back()->withErrors(['general' => 'Нет Redmine-пользователя']);

        $data = $request->validate([
            'project_id'     => 'required|integer',
            'tracker_id'     => 'required|integer',
            'subject'        => 'required|string|max:255',
            'description'    => 'nullable|string',
            'custom_fields'  => 'nullable|array',
            'custom_fields.*' => 'nullable',
            'attachments.*'   => 'file|max:20480'
        ]);

        $issue = $this->issues->createIssue($user, $data);
        if (!$issue) {
            return back()->withErrors(['general' => 'Не удалось создать задачу'])->withInput();
        }

        if (!empty($data['custom_fields'])) {
            $this->customFields->saveForIssue($issue, $data['custom_fields']);
        }
        // Загрузка вложений
        if ($request->hasFile('attachments')) {
            $uploads = $attachments->uploadMany($request->file('attachments'));
            if ($uploads) {
                $attachments->attachToIssue($issue->id, $uploads);
            }
        }

        return redirect()
            ->route('redmine.appeals', ['project' => $data['project_id']])
            ->with('ok', "Задача #{$issue->id} создана");
    }


    public function show(int $id)
    {
        $issue = RedmineIssue::with([
            'project:id,name',
            'status:id,name,is_closed',
            'tracker:id,name',
            'priority:id,name',
            'author:id,login,firstname,lastname',
            'assignee:id,login,firstname,lastname',
        ])->findOrFail($id);

        $history = $this->issues->getHistory($id);
        $customValues = $this->customFields->valuesForIssue($id);

        return view('redmine.issues.show', compact('issue', 'history', 'customValues'));
    }

    public function comment(Request $request, int $id, RedmineAttachmentService $attachments)
    {
        $user = RedmineUser::find(session('redmine_user_id'));
        if (!$user) return back()->withErrors(['general' => 'Нет Redmine-пользователя']);

        $data = $request->validate([
            'notes'         => 'required|string|min:1',
            'private'       => 'sometimes|boolean',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'file|max:20480',
        ]);

        $journal = $this->issues->addComment($id, $user, $data['notes'], $request->boolean('private'));
        if (!$journal) {
            return back()->withErrors(['general' => 'Не удалось добавить комментарий']);
        }

        if ($request->hasFile('attachments')) {
            $uploads = $attachments->uploadMany($request->file('attachments'));
            if ($uploads) {
                $attachments->attachToIssue($id, $uploads, $data['notes']);
            }
        }

        return back()->with('ok', 'Комментарий добавлен');
    }
}

<?php

namespace App\Http\Controllers\Redmine;

use App\Http\Controllers\Controller;
use App\Services\Redmine\RedmineCustomFieldService;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    public function __construct(
        protected RedmineCustomFieldService $fields,
    ) {}

    public function index(Request $request)
    {
        $projectId = (int) $request->query('project_id');
        $trackerId = (int) $request->query('tracker_id');

        if (!$projectId || !$trackerId) {
            return response()->json(['fields' => []]);
        }

        $fields = $this->fields
            ->forProjectAndTracker($projectId, $trackerId)
            ->map(function ($f) {
                return [
                    'id'              => $f->id,
                    'name'            => $f->name,
                    'field_format'    => $f->field_format,
                    'is_required'     => (bool) $f->is_required,
                    'default_value'   => $f->default_value,
                    'possible_values' => $f->possible_values,
                    'regexp'          => $f->regexp,
                    'min_length'      => $f->min_length,
                    'max_length'      => $f->max_length,
                ];
            })
            ->values();

        return response()->json(['fields' => $fields]);
    }
    // CustomFieldController
    public function trackers(Request $request, \App\Services\Redmine\RedmineIssueService $issues)
    {
        $projectId = (int) $request->query('project_id');
        if (!$projectId) {
            return response()->json(['trackers' => []]);
        }

        $trackers = $issues->getTrackersForProject($projectId)
            ->map(fn($t) => ['id' => $t->id, 'name' => $t->name])
            ->values();

        return response()->json(['trackers' => $trackers]);
    }
}

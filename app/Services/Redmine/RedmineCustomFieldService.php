<?php
// app/Services/Redmine/RedmineCustomFieldService.php

namespace App\Services\Redmine;

use App\Models\Redmine\RedmineCustomField;
use App\Models\Redmine\RedmineCustomValue;
use App\Models\Redmine\RedmineIssue;
use Illuminate\Support\Collection;

class RedmineCustomFieldService
{
    /**
     * Кастомные поля задачи, доступные для пары (project_id, tracker_id).
     */
public function forProjectAndTracker(int $projectId, int $trackerId): Collection
{
    return RedmineCustomField::query()
        ->forType('IssueCustomField')
        ->where('custom_fields.visible', 1)
        ->whereExists(function ($sub) use ($trackerId) {
            $sub->selectRaw('1')
                ->from('custom_fields_trackers as cft')
                ->whereColumn('cft.custom_field_id', 'custom_fields.id')
                ->where('cft.tracker_id', $trackerId);
        })
        ->orderBy('custom_fields.position')
        ->get();
}

    /**
     * Значения кастомных полей задачи (только непустые).
     */
    public function valuesForIssue(int $issueId): Collection
    {
        return RedmineCustomValue::query()
            ->where('customized_type', 'Issue')
            ->where('customized_id', $issueId)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->with('customField')
            ->get()
            ->keyBy(fn($v) => $v->customField?->name ?? $v->custom_field_id);
    }

  
    public function saveForIssue(RedmineIssue $issue, array $values): void
    {
        foreach ($values as $fieldId => $value) {
            $fieldId = (int) $fieldId;
            if ($fieldId <= 0) continue;

            $isEmpty = $value === null || $value === '' || (is_array($value) && empty($value));

            $existing = RedmineCustomValue::query()
                ->where('customized_type', 'Issue')
                ->where('customized_id', $issue->id)
                ->where('custom_field_id', $fieldId)
                ->first();

            if ($isEmpty) {
                $existing?->delete();
                continue;
            }

            if (is_array($value)) {
                $value = implode('|', $value);
            }

            if ($existing) {
                $existing->value = $value;
                $existing->save();
            } else {
                $cv = new RedmineCustomValue();
                $cv->setConnection('redmine');
                $cv->customized_type = 'Issue';
                $cv->customized_id   = $issue->id;
                $cv->custom_field_id = $fieldId;
                $cv->value           = $value;
                $cv->save();
            }
        }
    }
}

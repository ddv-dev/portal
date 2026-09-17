<?php
namespace App\Models\Redmine;

class RedmineCustomField extends RedmineModel
{
    protected $table = 'custom_fields';

    public function trackers()
    {
        return $this->belongsToMany(
            RedmineTracker::class,
            'custom_fields_trackers',
            'custom_field_id',
            'tracker_id'
        );
    }

    public function projects()
    {
        return $this->belongsToMany(
            RedmineProject::class,
            'custom_fields_projects',
            'custom_field_id',
            'project_id'
        );
    }

    public function scopeForType($q, string $type)
    {
        return $q->where('custom_fields.type', $type);
    }

    /**
     * possible_values в Redmine хранится как YAML-подобный текст, строки "- Value".
     */
    public function getPossibleValuesListAttribute(): array
    {
        $raw = $this->possible_values;
        if (blank($raw)) return [];

        $out = [];
        foreach (preg_split('/\r?\n/', trim($raw)) as $line) {
            if (preg_match('/^-\s*(.*)$/', $line, $m)) {
                $out[] = trim($m[1], " \"'");
            }
        }
        return $out;
    }
}
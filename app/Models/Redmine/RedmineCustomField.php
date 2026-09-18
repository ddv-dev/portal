<?php

namespace App\Models\Redmine;

class RedmineCustomField extends RedmineModel
{
    protected $table = 'custom_fields';

    // НЕ добавляй сюда possible_values в $casts — иначе аксессор перебьётся

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
     * possible_values в Redmine хранится как YAML-подобный текст "- Value".
     * Возвращаем массив строк.
     */
    public function getPossibleValuesAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', $value) as $line) {
            $line = trim($line);
            if ($line === '' || $line === '---') continue;
            $line = preg_replace('/^-\s*/', '', $line);
            $line = trim($line, " \t\"'");
            if ($line !== '') {
                $out[] = $line;
            }
        }
        return $out;
    }
}
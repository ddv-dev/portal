<?php
namespace App\Models\Redmine;

class RedmineCustomValue extends RedmineModel
{
    protected $table = 'custom_values';

    public function customField()
    {
        return $this->belongsTo(RedmineCustomField::class, 'custom_field_id');
    }
}
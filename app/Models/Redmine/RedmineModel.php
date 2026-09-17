<?php

namespace App\Models\Redmine;

use Illuminate\Database\Eloquent\Model;

abstract class RedmineModel extends Model
{
    protected $connection = 'redmine';

    public $timestamps = false;

    protected $guarded = [];
}
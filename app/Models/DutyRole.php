<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DutyRole extends Model
{
    protected $table = 'duty_role';

    protected $fillable = ['name'];

    public $timestamps = false;
}
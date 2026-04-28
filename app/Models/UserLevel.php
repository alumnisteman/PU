<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLevel extends Model
{
    protected $table = 'core_user_levels';
    protected $primaryKey = 'level_id';
    public $timestamps = false;
    protected $guarded = [];
}

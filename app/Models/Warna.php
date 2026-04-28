<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warna extends Model
{
    protected $table = 'pu_warna_tbl';
    protected $primaryKey = 'warna_id';
    public $timestamps = false;
    protected $guarded = [];
}

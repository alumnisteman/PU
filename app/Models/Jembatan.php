<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jembatan extends Model
{
    protected $table = 'pu_jembatan_tbl';
    protected $primaryKey = 'jembatan_id';
    public $timestamps = false;
    protected $guarded = [];

    public function details()
    {
        return $this->hasMany(JembatanDetail::class, 'detail_jembatan_id', 'jembatan_id');
    }
}

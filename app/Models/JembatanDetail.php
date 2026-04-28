<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JembatanDetail extends Model
{
    protected $table = 'pu_jembatan_detail_tbl';
    protected $primaryKey = 'detail_id';
    public $timestamps = false;
    protected $guarded = [];

    public function jembatan()
    {
        return $this->belongsTo(Jembatan::class, 'detail_jembatan_id', 'jembatan_id');
    }
}

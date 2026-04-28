<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JalanDetail extends Model
{
    protected $table = 'pu_jalan_detail_tbl';
    protected $primaryKey = 'detail_id';
    public $timestamps = false;
    protected $guarded = [];

    public function jalan()
    {
        return $this->belongsTo(Jalan::class, 'detail_jalan_id', 'jalan_id');
    }
}

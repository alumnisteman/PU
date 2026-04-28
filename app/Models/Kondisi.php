<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kondisi extends Model
{
    protected $table = 'pu_kondisi_tbl';
    protected $primaryKey = 'kondisi_id';
    public $timestamps = false;
    protected $guarded = [];

    public function jalan()
    {
        return $this->belongsTo(Jalan::class, 'kondisi_jalan_id', 'jalan_id');
    }
    
    public function warna()
    {
        return $this->belongsTo(Warna::class, 'kondisi_warna_id', 'warna_id');
    }
}

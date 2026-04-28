<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perkerasan extends Model
{
    protected $table = 'pu_perkerasan_tbl';
    protected $primaryKey = 'perkerasan_id';
    public $timestamps = false;
    protected $guarded = [];

    public function jalan()
    {
        return $this->belongsTo(Jalan::class, 'perkerasan_jalan_id', 'jalan_id');
    }
    
    public function warna()
    {
        return $this->belongsTo(Warna::class, 'perkerasan_warna_id', 'warna_id');
    }
}

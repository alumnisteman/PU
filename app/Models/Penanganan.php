<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penanganan extends Model
{
    protected $table = 'pu_penanganan_tbl';
    protected $primaryKey = 'penanganan_id';
    public $timestamps = false;
    protected $guarded = [];

    public function jalan()
    {
        return $this->belongsTo(Jalan::class, 'penanganan_jalan_id', 'jalan_id');
    }

    public function warna()
    {
        return $this->belongsTo(Warna::class, 'penanganan_warna_id', 'warna_id');
    }
}

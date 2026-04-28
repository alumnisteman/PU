<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jalan extends Model
{
    protected $table = 'pu_jalan_tbl';
    protected $primaryKey = 'jalan_id';
    public $timestamps = false;
    protected $guarded = [];

    public function details()
    {
        return $this->hasMany(JalanDetail::class, 'detail_jalan_id', 'jalan_id');
    }
    
    public function kondisis()
    {
        return $this->hasMany(Kondisi::class, 'kondisi_jalan_id', 'jalan_id');
    }

    public function perkerasans()
    {
        return $this->hasMany(Perkerasan::class, 'perkerasan_jalan_id', 'jalan_id');
    }

    public function penanganans()
    {
        return $this->hasMany(Penanganan::class, 'penanganan_jalan_id', 'jalan_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    protected $table = 'wil_kecamatan_tbl';
    protected $primaryKey = 'kecamatan_id';
    public $timestamps = false;

    protected $guarded = [];

    public function kota()
    {
        return $this->belongsTo(Kota::class, 'kecamatan_kota_id', 'kota_id');
    }

    public function kelurahans()
    {
        return $this->hasMany(Kelurahan::class, 'kelurahan_kecamatan_id', 'kecamatan_id');
    }
}

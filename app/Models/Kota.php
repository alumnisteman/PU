<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kota extends Model
{
    protected $table = 'wil_kota_tbl';
    protected $primaryKey = 'kota_id';
    public $timestamps = false;

    protected $guarded = [];

    public function propinsi()
    {
        return $this->belongsTo(Propinsi::class, 'kota_propinsi_id', 'propinsi_id');
    }

    public function kecamatans()
    {
        return $this->hasMany(Kecamatan::class, 'kecamatan_kota_id', 'kota_id');
    }
}

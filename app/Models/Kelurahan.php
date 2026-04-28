<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelurahan extends Model
{
    protected $table = 'wil_kelurahan_tbl';
    protected $primaryKey = 'kelurahan_id';
    public $timestamps = false;
    protected $guarded = [];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kelurahan_kecamatan_id', 'kecamatan_id');
    }
}

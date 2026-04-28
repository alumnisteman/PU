<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Propinsi extends Model
{
    protected $table = 'wil_propinsi_tbl';
    protected $primaryKey = 'propinsi_id';
    public $timestamps = false; // We'll handle dates manually since they use propinsi_dibuat_pada

    protected $guarded = [];

    public function kotas()
    {
        return $this->hasMany(Kota::class, 'kota_propinsi_id', 'propinsi_id');
    }
}

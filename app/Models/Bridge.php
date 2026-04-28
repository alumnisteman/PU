<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bridge extends Model
{
    protected $fillable = ['name', 'road_id', 'length_m', 'condition'];

    public function road()
    {
        return $this->belongsTo(Road::class);
    }
}

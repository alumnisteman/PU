<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoadMaterial extends Model
{
    protected $fillable = ['road_id', 'material_id', 'volume'];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function road()
    {
        return $this->belongsTo(RoadAsset::class, 'road_id');
    }
}

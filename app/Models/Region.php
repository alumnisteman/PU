<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = ['name', 'province', 'city', 'district'];

    public function roadAssets()
    {
        return $this->hasMany(RoadAsset::class);
    }
}

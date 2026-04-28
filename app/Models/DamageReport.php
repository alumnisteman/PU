<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageReport extends Model
{
    protected $fillable = [
        'road_asset_id', 'user_id', 'title', 'description', 
        'severity', 'status', 'latitude', 'longitude'
    ];

    public function roadAsset()
    {
        return $this->belongsTo(RoadAsset::class, 'road_asset_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(DamagePhoto::class);
    }
}

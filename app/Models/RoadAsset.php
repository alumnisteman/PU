<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoadAsset extends Model
{
    protected $table = 'road_assets';

    protected $fillable = [
        'region_id', 'latitude', 'longitude', 'elevation',
        'road_code', 'road_name', 'length_km', 'width_m',
        'description', 'condition_status', 'photo_url'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Logic Guard 1: Auto-trim road names
            if ($model->road_name) {
                $model->road_name = trim($model->road_name);
            }

            // Logic Guard 2: Normalize condition_status (Legacy support)
            $map = [
                'rusak' => 'rusak_berat',
                'baik' => 'baik',
                'sedang' => 'sedang',
                'rusak_ringan' => 'rusak_ringan',
                'rusak_berat' => 'rusak_berat'
            ];
            
            $status = strtolower($model->condition_status);
            $model->condition_status = $map[$status] ?? 'baik';
        });
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}

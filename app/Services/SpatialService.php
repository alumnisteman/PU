<?php

namespace App\Services;

use App\Models\Road;
use App\Models\DamageReport;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Support\Facades\DB;

class SpatialService
{
    /**
     * Cari jalan terdekat dan tempelkan (snap) koordinat GPS ke jalan tersebut.
     * Menggunakan ST_Distance dan proyeksi sederhana.
     */
    public function snapAndSaveDamage($lat, $lng, $damageType, $accuracy, $source = 'hp', $details = [])
    {
        // 1. Convert input to MySQL POINT string for queries
        $pointStr = "ST_GeomFromText('POINT($lat $lng)', 4326)";

        // 2. Find the closest road within ~20 meters (approx 0.0002 degrees)
        $closestRoad = Road::select('id', 'name', 'code', 'geom')
            ->selectRaw("ST_Distance(geom, $pointStr) as distance")
            ->whereNotNull('geom')
            ->having('distance', '<', 0.0002) // Roughly 20 meters
            ->orderBy('distance')
            ->first();

        if (!$closestRoad) {
            throw new \Exception("Koordinat di luar jangkauan ruas jalan (>20m). Titik ditolak untuk menjaga akurasi.");
        }

        // 3. MySQL 8.0 doesn't have ST_ClosestPoint natively like PostGIS, 
        // so we save the raw point for now, knowing it is within 20m of the road.
        // In a full PostGIS setup, we would do: ST_ClosestPoint(road.geom, input.geom)
        
        $report = DamageReport::create([
            'road_id' => $closestRoad->id,
            'damage_type' => $damageType,
            'geom' => new Point($lat, $lng, 4326),
            'accuracy' => $accuracy,
            'source' => $source,
            'details' => $details,
        ]);

        return [
            'status' => 'success',
            'snapped_to_road' => $closestRoad->name,
            'distance_degrees' => $closestRoad->distance,
            'report' => $report
        ];
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Road;
use App\Models\RoadAsset;

class RoadDataService
{
    /**
     * Register a new road asset across all relevant tables (road_assets & roads)
     * Ensuring data integrity and avoiding mismatches.
     */
    public static function registerRoad(array $data)
    {
        return DB::transaction(function() use ($data) {
            $name = $data['name'];
            $lat = $data['lat'];
            $lng = $data['lng'];
            $condition = $data['condition'] ?? 'baik';
            $length_km = ($data['length_m'] ?? 0) / 1000;
            $code = $data['code'] ?? 'R-' . strtoupper(substr(uniqid(), -4));

            // 1. Insert into road_assets (Detailed Asset Management)
            $assetId = DB::table('road_assets')->insertGetId([
                'road_name' => $name,
                'latitude' => $lat,
                'longitude' => $lng,
                'length_km' => $length_km,
                'width_m' => 6.0,
                'condition_status' => $condition,
                'road_code' => $code,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Insert into roads (SISMAP PULSE Integration)
            DB::table('roads')->updateOrInsert(
                ['id' => $assetId], // Sync ID if possible, or use name-based lookup
                [
                    'name' => $name,
                    'code' => $code,
                    'lat' => $lat,
                    'lng' => $lng,
                    'length_km' => $length_km,
                    'condition' => $condition,
                    'geometry' => json_encode(['type' => 'Point', 'coordinates' => [(float)$lng, (float)$lat]]),
                    'geom' => DB::raw("ST_SRID(ST_GeomFromText('LINESTRING($lng $lat, $lng $lat)'), 4326)"),
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())')
                ]
            );

            return $assetId;
        });
    }
}

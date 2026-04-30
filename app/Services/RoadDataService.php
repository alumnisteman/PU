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
            $name = trim($data['name']);
            // Normalize: Ensure "Jalan " prefix exists for consistent joining
            if (!str_starts_with(strtolower($name), 'jalan')) {
                $name = 'Jalan ' . $name;
            }

            $lat = $data['lat'];
            $lng = $data['lng'];
            $condition = $data['condition'] ?? 'baik';
            $length_km = ($data['length_m'] ?? 0) / 1000;
            $code = $data['code'] ?? 'R-' . strtoupper(substr(uniqid(), -4));
            $coords = $data['coordinates'] ?? [[$lng, $lat]];

            // Build WKT LINESTRING
            $wktPoints = [];
            foreach($coords as $c) {
                $wktPoints[] = "{$c[0]} {$c[1]}";
            }
            if (count($wktPoints) === 1) $wktPoints[] = $wktPoints[0];
            $wktLine = "LINESTRING(" . implode(", ", $wktPoints) . ")";

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
                ['name' => $name],
                [
                    'name' => $name,
                    'code' => $code,
                    'lat' => $lat,
                    'lng' => $lng,
                    'length_km' => $length_km,
                    'condition' => $condition,
                    'geometry' => json_encode(['type' => 'LineString', 'coordinates' => $coords]),
                    'geom' => DB::raw("ST_SRID(ST_GeomFromText('$wktLine'), 4326)"),
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );

            return $assetId;
        });
    }
}

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$updates = [
    1 => 'Jalan Pahlawan Revolusi',
    2 => 'Jalan Menteng',
    3 => 'Jalan Kapitan Patimurra',
    4 => 'Jalan Pasar Inpres Bastiong',
    5 => 'Jalan Pendidikan',
];

foreach ($updates as $id => $name) {
    // Get coordinate of one of the segments to center the asset correctly
    // ST_X is longitude (127), ST_Y is latitude (0.78)
    $segment = \Illuminate\Support\Facades\DB::selectOne("
        SELECT ST_Y(ST_StartPoint(geom)) as lat, ST_X(ST_StartPoint(geom)) as lng 
        FROM road_segments 
        WHERE name = ? 
        LIMIT 1
    ", [$name]);
    
    if ($segment) {
        $lat = $segment->lat; // Usually 0.78 or -0.6
        $lng = $segment->lng; // Usually 127.xxx
        
        // If they are flipped in MySQL for some reason:
        if ($lat > 90 || $lat < -90) {
            $lat = $segment->lng;
            $lng = $segment->lat;
        }

        \Illuminate\Support\Facades\DB::table('road_assets')
            ->where('id', $id)
            ->update([
                'road_name' => $name,
                'latitude' => $lat,
                'longitude' => $lng
            ]);
        echo "Updated Asset $id to $name at Lat: $lat, Lng: $lng\n";
    }
}

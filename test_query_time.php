<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$start = microtime(true);
$polygon = "POLYGON((0.79 127.38, 0.79 127.4, 0.82 127.4, 0.82 127.38, 0.79 127.38))";

$segments = \Illuminate\Support\Facades\DB::select("
    SELECT rs.id, rs.name, rs.highway, rs.area, 
           COALESCE(ra.condition_status, rs.condition) as `condition`, 
           rs.score, rs.length_m, ST_AsGeoJSON(rs.geom) as geometry,
           ra.id as asset_id, ra.photo_url, ra.created_at as asset_created_at
    FROM road_segments rs
    LEFT JOIN road_assets ra ON TRIM(LOWER(rs.name)) COLLATE utf8mb4_unicode_ci = TRIM(LOWER(ra.road_name)) COLLATE utf8mb4_unicode_ci
    WHERE ST_Intersects(rs.geom, ST_GeomFromText(?, 4326))
    LIMIT 5000
", [$polygon]);

$time = microtime(true) - $start;
echo "Found " . count($segments) . " segments in " . round($time, 4) . " seconds.\n";

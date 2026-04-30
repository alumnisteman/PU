<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$start = microtime(true);
$polygon = "POLYGON((0.79 127.38, 0.79 127.4, 0.82 127.4, 0.82 127.38, 0.79 127.38))";

$segments = \Illuminate\Support\Facades\DB::select("
    SELECT id, name, highway, area, condition, score, length_m
    FROM road_segments
    WHERE ST_Intersects(geom, ST_GeomFromText(?, 4326))
    ORDER BY FIELD(highway, 'trunk', 'primary', 'secondary', 'tertiary', 'residential', 'unclassified')
    LIMIT 5000
", [$polygon]);

$time = microtime(true) - $start;
echo "Found " . count($segments) . " segments in " . round($time, 4) . " seconds with ORDER BY.\n";

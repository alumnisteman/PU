<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$segments = \Illuminate\Support\Facades\DB::select("
    SELECT DISTINCT name 
    FROM road_segments 
    WHERE name IS NOT NULL AND name != 'Jalan Tanpa Nama'
    LIMIT 20
");
print_r($segments);

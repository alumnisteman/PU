<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$segments = \Illuminate\Support\Facades\DB::select("SELECT id, name FROM road_segments WHERE name LIKE '%Sultan%'");
print_r($segments);

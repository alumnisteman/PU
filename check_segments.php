<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$segments = DB::table('road_segments')
    ->select('name', 'condition')
    ->whereIn('name', ['Jalan Sultan Khairun', 'Jalan Merdeka'])
    ->get();

print_r($segments->toArray());

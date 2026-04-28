<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$dbs = ['sismap', 'k4701531_sismap', 'pu_halsel'];

foreach ($dbs as $db) {
    echo "--- MENGECEK DATABASE: $db ---\n";
    config(['database.connections.mysql.database' => $db]);
    DB::purge('mysql');

    try {
        // Cek road_assets
        $count = DB::table('road_assets')->where('condition_status', '!=', 'baik')->count();
        echo "Tabel road_assets: Ada $count titik rusak.\n";
        
        // Cek pu_kondisi_tbl
        $countPu = DB::table('pu_kondisi_tbl')->where('kondisi_tipe', '!=', 'BAIK')->count();
        echo "Tabel pu_kondisi_tbl: Ada $countPu titik rusak.\n";

        if ($count > 0) {
            $sample = DB::table('road_assets')->where('condition_status', '!=', 'baik')->first();
            echo "Sampel road_assets: {$sample->road_name} ({$sample->condition_status}) at {$sample->latitude},{$sample->longitude}\n";
        }
    } catch (\Exception $e) {
        echo "Error atau tabel tidak ada di $db\n";
    }
    echo "\n";
}

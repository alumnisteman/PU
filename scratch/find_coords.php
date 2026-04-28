<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Paksa koneksi ke database k4701531_sismap
config(['database.connections.mysql.database' => 'k4701531_sismap']);
DB::purge('mysql');

echo "--- MENCARI KOLOM KOORDINAT DI SELURUH DATABASE k4701531_sismap ---\n";
$results = DB::select("SELECT table_name, column_name FROM information_schema.columns WHERE table_schema = 'k4701531_sismap' AND (column_name LIKE '%lat%' OR column_name LIKE '%lon%' OR column_name LIKE '%llh%' OR column_name LIKE '%geo%')");

foreach($results as $r) {
    $tbl = isset($r->table_name) ? $r->table_name : $r->TABLE_NAME;
    $col = isset($r->column_name) ? $r->column_name : $r->COLUMN_NAME;
    echo "Tabel: {$tbl} | Kolom: {$col}\n";
}

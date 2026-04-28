<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- MENGHITUNG TITIK RUSAK DENGAN KOORDINAT ---\n";
$count = DB::table('pu_kondisi_tbl')
    ->join('pu_jalan_tbl', 'pu_kondisi_tbl.kondisi_jalan_id', '=', 'pu_jalan_tbl.jalan_id')
    ->where('pu_kondisi_tbl.kondisi_tipe', '!=', 'BAIK')
    ->whereNotNull('pu_jalan_tbl.jalan_llh')
    ->where('pu_jalan_tbl.jalan_llh', '!=', '')
    ->count();

echo "Total titik rusak siap tayang: $count\n";

$samples = DB::table('pu_kondisi_tbl')
    ->join('pu_jalan_tbl', 'pu_kondisi_tbl.kondisi_jalan_id', '=', 'pu_jalan_tbl.jalan_id')
    ->where('pu_kondisi_tbl.kondisi_tipe', '!=', 'BAIK')
    ->whereNotNull('pu_jalan_tbl.jalan_llh')
    ->where('pu_jalan_tbl.jalan_llh', '!=', '')
    ->limit(3)
    ->get();

foreach($samples as $s) {
    echo "Nama: {$s->jalan_nama} | LLH: {$s->jalan_llh}\n";
}


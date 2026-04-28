<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DamageReport;
use Illuminate\Support\Facades\DB;

echo "SINKRONISASI FINAL: Menyamakan Dashboard dengan SISMAP PULSE (Data PU)...\n";

DB::statement('SET FOREIGN_KEY_CHECKS=0');
DamageReport::truncate();
DB::table('damage_photos')->truncate();
DB::table('ai_analysis')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1');

$user = DB::table('core_users')->select('user_id')->first();
$userId = $user ? $user->user_id : 1;

// Ambil data dari tabel PU
$puData = DB::table('pu_kondisi_tbl')
    ->join('pu_jalan_tbl', 'pu_kondisi_tbl.kondisi_jalan_id', '=', 'pu_jalan_tbl.jalan_id')
    ->where('pu_kondisi_tbl.kondisi_tipe', '!=', 'BAIK')
    ->whereNotNull('pu_jalan_tbl.jalan_llh')
    ->get();

$count = 0;
foreach ($puData as $data) {
    $llh = $data->jalan_llh;
    if (empty($llh)) continue;

    // Cari koordinat yang valid untuk Maluku Utara (biasanya Lintang 0-2, Bujur 124-129)
    $coords = preg_split('/[\s,]+/', $llh);
    $lat = null;
    $lon = null;

    // Loop untuk mencari pasangan lat,lon yang masuk akal di Maluku Utara
    for ($i = 0; $i < count($coords) - 1; $i++) {
        $c1 = (float)$coords[$i];
        $c2 = (float)$coords[$i+1];
        
        // Cek apakah c1 adalah Lat (-2 s/d 3) dan c2 adalah Lon (124 s/d 129)
        if ($c1 > -3 && $c1 < 4 && $c2 > 124 && $c2 < 130) {
            $lat = $c1;
            $lon = $c2;
            break;
        }
    }

    if ($lat !== null && $lon !== null) {
        $severity = 'ringan';
        if ($data->kondisi_tipe == 'RUSAK BERAT') $severity = 'berat';
        elseif ($data->kondisi_tipe == 'RUSAK RINGAN' || $data->kondisi_tipe == 'SEDANG') $severity = 'sedang';

        DamageReport::create([
            'road_asset_id' => $data->jalan_id,
            'title' => 'Data PU: ' . $data->jalan_nama,
            'description' => 'Sinkronisasi riil dari SISMAP PULSE (Kondisi: ' . $data->kondisi_tipe . ')',
            'severity' => $severity,
            'status' => 'open',
            'latitude' => $lat,
            'longitude' => $lon,
            'user_id' => $userId
        ]);
        $count++;
    }
}

echo "BERHASIL! $count titik dari SISMAP PULSE (Data PU) telah dipasang di Monitor.\n";

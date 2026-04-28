<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RoadAsset;
use App\Models\DamageReport;
use Illuminate\Support\Facades\DB;

echo "Memulai sinkronisasi data riil...\n";

// Ambil User ID yang valid dari tabel core_users
$user = DB::table('core_users')->select('user_id')->first();
$userId = $user ? $user->user_id : 1;

// Ambil semua aset jalan yang punya koordinat
$roads = RoadAsset::whereNotNull('latitude')->where('latitude', '!=', 0)->get();
$count = 0;

foreach ($roads as $road) {
    // Masukkan ke damage_reports sebagai data 'valid' untuk dashboard
    DamageReport::create([
        'road_asset_id' => $road->id,
        'title' => 'Laporan Kondisi: ' . $road->nama_ruas,
        'description' => 'Data disinkronkan dari pemetaan aset jalan Maluku Utara.',
        'severity' => (str_contains(strtolower($road->kondisi), 'rusak') ? 'berat' : 
                      (str_contains(strtolower($road->kondisi), 'sedang') ? 'sedang' : 'ringan')),
        'status' => 'open',
        'latitude' => $road->latitude,
        'longitude' => $road->longitude,
        'user_id' => $userId
    ]);
    $count++;
}

echo "BERHASIL! $count titik data koordinat nyata telah muncul di peta Dashboard.\n";

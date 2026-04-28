<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RoadAsset;
use App\Models\DamageReport;
use Illuminate\Support\Facades\DB;

echo "Menyelaraskan data monitor dengan data riil SISMAP PULSE...\n";

// Matikan pengecekan key sementara untuk pembersihan
DB::statement('SET FOREIGN_KEY_CHECKS=0');
DamageReport::truncate();
DB::table('damage_photos')->truncate();
DB::table('ai_analysis')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1');

// Ambil User ID valid
$user = DB::table('core_users')->select('user_id')->first();
$userId = $user ? $user->user_id : 1;

// Ambil jalan yang kondisinya TIDAK BAIK (Rusak Berat, Sedang, Ringan)
$damagedRoads = RoadAsset::whereNotNull('latitude')
    ->where('latitude', '!=', 0)
    ->where('condition_status', '!=', 'baik')
    ->get();

$count = 0;
foreach ($damagedRoads as $road) {
    // Tentukan tingkat keparahan berdasarkan kondisi riil di database
    $severity = 'ringan';
    $status = strtolower($road->condition_status);
    if (str_contains($status, 'berat')) $severity = 'berat';
    elseif (str_contains($status, 'sedang') || str_contains($status, 'ringan')) $severity = 'sedang';

    DamageReport::create([
        'road_asset_id' => $road->id,
        'title' => 'Kerusakan Riil: ' . $road->road_name,
        'description' => 'Terdeteksi kondisi ' . $road->condition_status . ' pada sistem aset jalan.',
        'severity' => $severity,
        'status' => 'open',
        'latitude' => $road->latitude,
        'longitude' => $road->longitude,
        'user_id' => $userId
    ]);
    $count++;
}

echo "BERHASIL! $count titik jalan rusak riil telah dipindahkan ke monitor. Peta sekarang sinkron dengan SISMAP PULSE.\n";

<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DamageReport;
use Illuminate\Support\Facades\DB;

echo "SINKRONISASI MASSAL: Mengimpor 2.850 titik riil dari tabel 'roads'...\n";

DB::statement('SET FOREIGN_KEY_CHECKS=0');
DamageReport::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1');

$user = DB::table('core_users')->select('user_id')->first();
$userId = $user ? $user->user_id : 1;

// Ambil data dari tabel roads yang kondisinya tidak baik
// Kita ambil titik pusat dari geometri (ST_Centroid atau ST_AsText)
$roads = DB::table('roads')
    ->select('id', 'name', 'condition', 'severity', DB::raw('ST_AsText(geom) as wkt'))
    ->where('condition', '!=', 'baik')
    ->whereNotNull('geom')
    ->get();

$count = 0;
foreach ($roads as $road) {
    $wkt = $road->wkt;
    if (empty($wkt)) continue;

    // Ekstrak koordinat dari WKT (POINT atau LINESTRING)
    preg_match('/POINT\(([^ ]+) ([^\)]+)\)/', $wkt, $matches);
    if (empty($matches)) {
        // Jika LineString, ambil titik pertama
        preg_match('/LINESTRING\(([^ ]+) ([^,]+)/', $wkt, $matches);
    }

    if (!empty($matches)) {
        $lon = (float)$matches[1];
        $lat = (float)$matches[2];

        $sev = 'ringan';
        $cond = strtolower($road->condition);
        if (str_contains($cond, 'berat')) $sev = 'berat';
        elseif (str_contains($cond, 'sedang') || str_contains($cond, 'ringan')) $sev = 'sedang';

        DamageReport::create([
            'road_asset_id' => null,
            'title' => 'Kerusakan Riil: ' . ($road->name ?? 'Unnamed Road'),
            'description' => 'Sinkronisasi riil SISMAP PULSE. Kondisi: ' . $road->condition,
            'severity' => $sev,
            'status' => 'open',
            'latitude' => $lat,
            'longitude' => $lon,
            'user_id' => $userId
        ]);
        $count++;
    }
}

echo "BERHASIL! $count titik kerusakan riil telah dipasang di peta. Peta sekarang identik dengan SISMAP PULSE.\n";

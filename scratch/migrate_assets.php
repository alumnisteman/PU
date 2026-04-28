<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RoadAsset;
use Illuminate\Support\Facades\DB;

echo "MIGRASI TOTAL: Memasukkan 2.850+ jalan ke Sistem Manajemen Admin...\n";

// Ambil semua data dari tabel roads yang belum ada di road_assets
// Kita gunakan 'code' atau 'id' sebagai pembanding
$roads = DB::table('roads')->whereNotNull('geom')->get();

$count = 0;
foreach ($roads as $road) {
    $code = $road->code ?? ('RD-' . str_pad($road->id, 5, '0', STR_PAD_LEFT));
    
    // Cek apakah sudah ada berdasarkan kode unik
    $exists = RoadAsset::where('road_code', $code)->exists();

    if (!$exists) {
        // Ekstrak koordinat dari Geom
        $wkt = DB::table('roads')->where('id', $road->id)->select(DB::raw('ST_AsText(geom) as wkt'))->first()->wkt;
        $lat = 0; $lon = 0;
        if (preg_match('/POINT\(([^ ]+) ([^\)]+)\)/', $wkt, $matches)) {
            $lon = (float)$matches[1]; $lat = (float)$matches[2];
        } elseif (preg_match('/LINESTRING\(([^ ]+) ([^,]+)/', $wkt, $matches)) {
            $lon = (float)$matches[1]; $lat = (float)$matches[2];
        }

        if ($lat != 0) {
            RoadAsset::create([
                'region_id' => $road->region_id ?? 1,
                'road_code' => $road->code ?? ('RD-' . str_pad($road->id, 5, '0', STR_PAD_LEFT)),
                'road_name' => $road->name ?? 'Jalan Tanpa Nama (' . $road->id . ')',
                'length_km' => $road->length_km ?? 0,
                'width_m' => $road->width_m ?? 0,
                'latitude' => $lat,
                'longitude' => $lon,
                'condition_status' => $road->condition ?? 'baik',
                'description' => $road->description ?? 'Migrasi otomatis dari SISMAP PULSE',
            ]);
            $count++;
        }
    }
}

echo "BERHASIL! $count jalan baru telah terdaftar di Sistem Admin dan siap untuk dikelola.\n";

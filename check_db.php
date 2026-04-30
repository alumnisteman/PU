<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

echo "=== DIAGNOSA DATA JALAN ===\n";

$roads = DB::table('roads')->where('name', 'LIKE', '%AM KAMARUDDIN%')->get();
if ($roads->isEmpty()) {
    echo "❌ ERROR: Jalan 'AM KAMARUDDIN' TIDAK DITEMUKAN di tabel 'roads'.\n";
} else {
    foreach ($roads as $r) {
        echo "✅ DITEMUKAN: " . $r->name . "\n";
        echo "   - Geometry: " . ($r->geometry ? "ADA (JSON)" : "KOSONG") . "\n";
        echo "   - Geom (Spatial): " . ($r->geom ? "ADA (BLOB)" : "KOSONG") . "\n";
    }
}

$assets = DB::table('road_assets')->where('road_name', 'LIKE', '%AM KAMARUDDIN%')->get();
if ($assets->isEmpty()) {
    echo "❌ ERROR: Jalan 'AM KAMARUDDIN' TIDAK DITEMUKAN di tabel 'road_assets'.\n";
} else {
    foreach ($assets as $a) {
        echo "✅ ASSET DITEMUKAN: " . $a->road_name . " (ID: " . $a->id . ")\n";
    }
}

echo "=== SELESAI ===\n";

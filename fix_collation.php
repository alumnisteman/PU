<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

echo "=== MEMULAI PERBAIKAN DATABASE ===\n";

try {
    // 1. Ubah Tabel road_assets
    echo "Updating road_assets...\n";
    DB::statement("ALTER TABLE road_assets CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // 2. Ubah Tabel roads
    echo "Updating roads...\n";
    DB::statement("ALTER TABLE roads CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // 3. Ubah Tabel regions
    echo "Updating regions...\n";
    DB::statement("ALTER TABLE regions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    echo "✅ SUKSES: Semua tabel sekarang menggunakan utf8mb4_unicode_ci.\n";
} catch (\Exception $e) {
    echo "❌ GAGAL: " . $e->getMessage() . "\n";
}

echo "=== SELESAI ===\n";

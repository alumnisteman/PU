<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tables = ['road_assets', 'road_segments', 'roads', 'pu_jalan_tbl'];

foreach($tables as $table) {
    echo "--- KOLOM DI TABEL: $table ---\n";
    $columns = Schema::getColumnListing($table);
    echo implode(", ", $columns) . "\n\n";
}

echo "--- CONTOH DATA road_assets ---\n";
$asset = DB::table('road_assets')->limit(1)->first();
print_r($asset);

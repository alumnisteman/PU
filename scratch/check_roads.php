<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- MENGHITUNG KERUSAKAN DI TABEL 'roads' ---\n";
try {
    $count = DB::table('roads')->where('condition', '!=', 'baik')->count();
    echo "Total jalan rusak di tabel 'roads': $count\n";

    if ($count > 0) {
        $samples = DB::table('roads')->where('condition', '!=', 'baik')->limit(3)->get();
        foreach($samples as $s) {
            echo "Nama: {$s->name} | Kondisi: {$s->condition} | Lat: {$s->lat} | Lng: {$s->lng}\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

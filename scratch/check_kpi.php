<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

// 1. Hitung per kondisi di tabel roads
echo "=== DISTRIBUSI KONDISI JALAN (tabel roads) ===\n";
$counts = DB::table('roads')->select(DB::raw('`condition`, count(*) as total'))->groupBy('condition')->orderByDesc('total')->get();
foreach($counts as $c) {
    echo strtoupper($c->condition) . ": " . $c->total . "\n";
}

echo "\nTotal jalan: " . DB::table('roads')->count() . "\n";

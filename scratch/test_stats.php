<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

try {
    $stats = DB::table('roads')
        ->select(DB::raw('`condition`, count(*) as total'))
        ->groupBy('condition')
        ->get()
        ->mapWithKeys(function ($item) {
            return [strtolower($item->condition) => $item->total];
        });

    $response = [
        'baik'         => $stats->get('baik') ?? 0,
        'sedang'       => $stats->get('sedang') ?? 0,
        'rusak_ringan' => $stats->get('rusak_ringan') ?? 0,
        'rusak_berat'  => $stats->get('rusak_berat') ?? 0,
        'total'        => DB::table('roads')->count(),
    ];
    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}

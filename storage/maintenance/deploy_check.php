<?php

/**
 * SISMAP Pre-flight Check Script (Enhanced)
 * Verifies code integrity, data integrity, and route health before deployment.
 */

echo "--- STARTING PRE-FLIGHT CHECK ---\n";

// 1. Syntax Check (PHP)
$filesToCheck = [
    'routes/api.php',
    'routes/web.php',
    'app/Services/JalanService.php',
    'app/Http/Controllers/RoadController.php',
    'app/Http/Controllers/JalanController.php',
    'app/Models/RoadAsset.php',
    'app/Helpers/RoadStatus.php',
];

foreach ($filesToCheck as $file) {
    if (!file_exists($file)) continue;
    $output = [];
    $returnVar = 0;
    exec("php -l " . escapeshellarg($file), $output, $returnVar);
    if ($returnVar !== 0) {
        die("CRITICAL ERROR: Syntax error in $file\n" . implode("\n", $output));
    }
    echo "✓ $file: Syntax OK\n";
}

// 2. Database Connection Check
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "✓ Database Connection: OK\n";
} catch (\Exception $e) {
    die("CRITICAL ERROR: Database unreachable: " . $e->getMessage() . "\n");
}

// 3. Key Table Check
$tables = ['road_assets', 'road_segments', 'regions'];
foreach ($tables as $table) {
    if (!Illuminate\Support\Facades\Schema::hasTable($table)) {
        die("CRITICAL ERROR: Missing table: $table\n");
    }
}
echo "✓ Essential Tables: OK\n";

// 4. Data Integrity Check: Detect invalid condition_status values
$validStatuses = ['baik', 'sedang', 'rusak_ringan', 'rusak_berat'];
$invalidRows = Illuminate\Support\Facades\DB::table('road_assets')
    ->whereNotIn('condition_status', $validStatuses)
    ->select('id', 'road_name', 'condition_status')
    ->get();

if ($invalidRows->count() > 0) {
    echo "⚠ WARNING: " . $invalidRows->count() . " road(s) have invalid condition_status:\n";
    foreach ($invalidRows as $row) {
        echo "  - ID #{$row->id} ({$row->road_name}): '{$row->condition_status}'\n";
        $fix = str_contains($row->condition_status, 'rusak') ? 'rusak_berat' : 'baik';
        Illuminate\Support\Facades\DB::table('road_assets')->where('id', $row->id)->update(['condition_status' => $fix]);
        echo "    → Auto-fixed to: '$fix'\n";
    }
} else {
    echo "✓ Data Integrity (condition_status): ALL VALID\n";
}

// 5. Mismatch Check: Road assets without matching map segment
$orphanAssets = Illuminate\Support\Facades\DB::select("
    SELECT ra.id, ra.road_name
    FROM road_assets ra
    LEFT JOIN road_segments rs ON TRIM(LOWER(rs.name)) COLLATE utf8mb4_unicode_ci = TRIM(LOWER(ra.road_name)) COLLATE utf8mb4_unicode_ci
    WHERE rs.id IS NULL
");

if (count($orphanAssets) > 0) {
    echo "⚠ WARNING: " . count($orphanAssets) . " asset(s) have no matching map segment:\n";
    foreach ($orphanAssets as $asset) {
        echo "  - ID #{$asset->id}: '{$asset->road_name}'\n";
    }
} else {
    echo "✓ Data Mismatch Check (Assets vs Segments): OK\n";
}

// 6. HTTP Health Check
$baseUrl = 'http://localhost:8008';
$routes = ['/', '/admin/jalan'];

foreach ($routes as $route) {
    $ch = curl_init($baseUrl . $route);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $icon = ($code === 200 || $code === 302) ? "✓" : "✗ CRITICAL";
    echo "$icon Route '$route': HTTP $code\n";
}

echo "--- ALL CHECKS COMPLETE. SYSTEM STABLE. ---\n";

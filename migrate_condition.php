<?php
/**
 * SISMAP Database Migration Script
 * Converts condition_status from ENUM to VARCHAR and normalizes legacy data.
 * Run once: php migrate_condition.php
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$db = Illuminate\Support\Facades\DB::connection();

echo "--- SISMAP Database Migration ---\n";

// Step 1: Alter column from ENUM to VARCHAR to support all statuses
try {
    $db->statement("ALTER TABLE road_assets MODIFY COLUMN condition_status VARCHAR(20) NOT NULL DEFAULT 'baik'");
    echo "✓ Step 1: condition_status column changed from ENUM to VARCHAR(20)\n";
} catch (\Exception $e) {
    echo "! Step 1 skipped (possibly already done): " . $e->getMessage() . "\n";
}

// Step 2: Normalize all legacy 'rusak' values to 'rusak_berat'
$updated = $db->table('road_assets')
    ->where('condition_status', 'rusak')
    ->update(['condition_status' => 'rusak_berat']);
echo "✓ Step 2: Normalized $updated row(s) from 'rusak' → 'rusak_berat'\n";

// Step 3: Verify all values are now valid
$valid = ['baik', 'sedang', 'rusak_ringan', 'rusak_berat'];
$invalid = $db->table('road_assets')
    ->whereNotIn('condition_status', $valid)
    ->count();

if ($invalid > 0) {
    echo "⚠ WARNING: $invalid row(s) still have invalid status. Auto-fixing to 'baik'...\n";
    $db->table('road_assets')
        ->whereNotIn('condition_status', $valid)
        ->update(['condition_status' => 'baik']);
} else {
    echo "✓ Step 3: All condition_status values are valid\n";
}

// Step 4: Verify final state
$counts = $db->table('road_assets')
    ->select('condition_status', $db->raw('count(*) as total'))
    ->groupBy('condition_status')
    ->get();

echo "\nFinal Data Summary:\n";
foreach ($counts as $row) {
    echo "  - {$row->condition_status}: {$row->total} road(s)\n";
}

echo "\n--- MIGRATION COMPLETE ---\n";

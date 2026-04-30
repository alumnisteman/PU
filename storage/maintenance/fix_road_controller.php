<?php
$path = '/var/www/sismap/app/Http/Controllers/RoadController.php';
$content = file_get_contents($path);

// The correct line should be:
$newLine = "            ->select('village_code as name', DB::raw('SUM(length_m) as total_m'), DB::raw('SUM(CASE WHEN `condition` LIKE \"rusak%\" THEN length_m ELSE 0 END) as rusak_m'))";

// Find the line that starts with ->select('village_code as name'
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (strpos($line, "->select('village_code as name'") !== false) {
        $lines[$i] = $newLine;
        break;
    }
}

file_put_contents($path, implode("\n", $lines));
echo "Fixed RoadController.php thoroughly\n";

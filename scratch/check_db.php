<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['core_users', 'damage_reports'];
foreach ($tables as $table) {
    $status = DB::select("SHOW TABLE STATUS LIKE '$table'");
    if ($status) {
        $row = $status[0];
        echo "Table: $table | Engine: {$row->Engine} | Collation: {$row->Collation}\n";
    } else {
        echo "Table: $table NOT FOUND\n";
    }
}

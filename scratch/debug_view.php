<?php
// Debug script - capture view render error
chdir('/var/www/sismap');
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Boot the app
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $rendered = view('admin.dashboard')->render();
    echo "SUCCESS - View rendered OK, length: " . strlen($rendered) . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

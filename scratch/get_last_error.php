<?php
$logPath = '/var/www/sismap/storage/logs/laravel.log';
if (!file_exists($logPath)) {
    echo "Log file not found.\n";
    exit;
}
$lines = file($logPath);
$lines = array_reverse($lines);
$errors = [];
foreach ($lines as $line) {
    if (strpos($line, '.ERROR:') !== false) {
        $errors[] = $line;
        if (count($errors) >= 5) break;
    }
}
foreach ($errors as $error) {
    echo $error;
}

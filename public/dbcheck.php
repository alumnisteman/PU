<?php
// Debug: cek isi DB langsung
// Akses: http://192.168.1.7:8008/dbcheck.php
$env  = parse_ini_file(__DIR__ . '/../.env');
$conn = new mysqli(
    $env['DB_HOST'] ?? '127.0.0.1',
    $env['DB_USERNAME'] ?? '',
    $env['DB_PASSWORD'] ?? '',
    $env['DB_DATABASE'] ?? '',
    (int)($env['DB_PORT'] ?? 3306)
);
if ($conn->connect_error) die("DB Error: " . $conn->connect_error);
$conn->set_charset('utf8mb4');

header('Content-Type: text/plain; charset=utf-8');

// Tables
$res = $conn->query("SHOW TABLES");
echo "=== TABLES ===\n";
while($r = $res->fetch_array()) echo $r[0]."\n";

// Regions
echo "\n=== REGIONS (top 15) ===\n";
$res = $conn->query("SELECT id,province,city,district FROM regions LIMIT 15");
if ($res) while($r = $res->fetch_assoc()) echo implode(' | ', $r)."\n";
else echo "Table not found or error\n";

// Road assets
echo "\n=== ROAD_ASSETS (top 10) ===\n";
$res = $conn->query("SELECT id,region_id,road_name,condition_status FROM road_assets LIMIT 10");
if ($res) while($r = $res->fetch_assoc()) echo implode(' | ', $r)."\n";
else echo "Table not found or error\n";

$conn->close();

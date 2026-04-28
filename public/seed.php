<?php
/**
 * Sismap - Direct DB Seeder
 * Akses: http://192.168.1.7:8008/seed.php
 * Hapus file ini setelah selesai.
 */

// Hardcoded credentials for remote execution
$host = '127.0.0.1';
$port = 3306;
$db   = 'sismap';
$user = 'k4701531_all';
$pass = 'mushabdatabaseall';

$conn = new mysqli($host, $user, $pass, $db, (int)$port);
if ($conn->connect_error) {
    die("<b>Koneksi gagal:</b> " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

$log = [];

// ── 1. Buat tabel regions ──────────────────────────────────────────────────
$conn->query("CREATE TABLE IF NOT EXISTS regions (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    province VARCHAR(100),
    city     VARCHAR(100),
    district VARCHAR(100)
)");
$log[] = "✅ Tabel regions siap.";

// ── 2. Buat tabel road_assets ──────────────────────────────────────────────
$conn->query("CREATE TABLE IF NOT EXISTS road_assets (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    region_id        INT,
    latitude         DECIMAL(10,7),
    longitude        DECIMAL(11,7),
    elevation        DECIMAL(10,2),
    road_code        VARCHAR(50),
    road_name        VARCHAR(200),
    length_km        DECIMAL(10,2),
    width_m          DECIMAL(10,2),
    description      TEXT,
    condition_status ENUM('Baik','Rusak Ringan','Rusak Berat') DEFAULT 'Baik',
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
$log[] = "✅ Tabel road_assets siap.";

// ── 3. Hapus data lama ─────────────────────────────────────────────────────
$conn->query("SET FOREIGN_KEY_CHECKS=0");
$conn->query("TRUNCATE TABLE road_assets");
$conn->query("TRUNCATE TABLE regions");
$conn->query("ALTER TABLE regions AUTO_INCREMENT = 1");
$conn->query("SET FOREIGN_KEY_CHECKS=1");
$log[] = "✅ Data lama dihapus.";

// ── 4. Insert regions (Ternate + Sofifi) ───────────────────────────────────
$regions = [
    ['Maluku Utara', 'Ternate', 'Ternate Tengah'],
    ['Maluku Utara', 'Ternate', 'Ternate Tengah'],
    ['Maluku Utara', 'Ternate', 'Ternate Tengah'],
    ['Maluku Utara', 'Ternate', 'Ternate Utara'],
    ['Maluku Utara', 'Ternate', 'Ternate Utara'],
    ['Maluku Utara', 'Ternate', 'Ternate Utara'],
    ['Maluku Utara', 'Ternate', 'Ternate Selatan'],
    ['Maluku Utara', 'Ternate', 'Ternate Selatan'],
    ['Maluku Utara', 'Ternate', 'Ternate Selatan'],
    ['Maluku Utara', 'Ternate', 'Ternate Barat'],
    ['Maluku Utara', 'Ternate', 'Ternate Barat'],
    ['Maluku Utara', 'Sofifi',  'Oba Utara'],
    ['Maluku Utara', 'Sofifi',  'Oba'],
];

$stmt = $conn->prepare("INSERT INTO regions (province, city, district) VALUES (?,?,?)");
foreach ($regions as [$p, $c, $d]) {
    $stmt->bind_param('sss', $p, $c, $d);
    $stmt->execute();
}
$stmt->close();
$log[] = "✅ " . count($regions) . " regions Ternate & Sofifi dimasukkan.";

// ── 5. Insert road_assets ──────────────────────────────────────────────────
$roads = [
    // region_id, lat, lon, elev, code, name, km, m, desc
    [1,  -0.7901200, 127.3841100, 10, 'TRT-TGH-001', 'Jalan Sultan Khairun',  2.1, 6,   'Jalan pusat kota'],
    [2,  -0.7895500, 127.3822200, 12, 'TRT-TGH-002', 'Jalan Merdeka',         1.5, 5,   'Dekat area pasar'],
    [3,  -0.7889000, 127.3835000,  9, 'TRT-TGH-003', 'Jalan Pahlawan',        1.2, 4,   'Akses sekolah'],
    [4,  -0.7564300, 127.3721900, 11, 'TRT-UTR-001', 'Jalan Kasturian Raya',  1.8, 5.5, 'Permukiman warga'],
    [5,  -0.7521000, 127.3705000, 13, 'TRT-UTR-002', 'Jalan Dufa-Dufa',       2.3, 6,   'Jalan penghubung'],
    [6,  -0.7508800, 127.3690000, 14, 'TRT-UTR-003', 'Jalan Akehuda',         1.9, 5,   'Akses nelayan'],
    [7,  -0.8012200, 127.3659000,  8, 'TRT-SLT-001', 'Jalan Bastiong',        2.8, 7,   'Jalan utama selatan'],
    [8,  -0.8031100, 127.3674000,  7, 'TRT-SLT-002', 'Jalan Gambesi',         2.0, 6,   'Dekat bandara'],
    [9,  -0.8045500, 127.3699000,  9, 'TRT-SLT-003', 'Jalan Sasa',            1.7, 5,   'Permukiman'],
    [10, -0.7701200, 127.3501000, 15, 'TRT-BRT-001', 'Jalan Sulamadaha',      3.2, 6,   'Akses wisata'],
    [11, -0.7680000, 127.3480000, 16, 'TRT-BRT-002', 'Jalan Tobololo',        2.4, 5,   'Jalan pesisir'],
    [12,  0.5301200, 127.5672000, 20, 'SFF-OBA-001', 'Jalan Oba Utara Raya',  4.1, 7,   'Jalan utama Sofifi'],
    [13,  0.5218000, 127.5590000, 18, 'SFF-OBA-002', 'Jalan Sofifi-Ternate',  3.8, 6,   'Penghubung kota'],
];



// Simpler approach with direct query
$conn->query("DELETE FROM road_assets");
$conn->query("ALTER TABLE road_assets AUTO_INCREMENT = 1");

foreach ($roads as [$rid,$lat,$lon,$elv,$code,$name,$km,$m,$desc]) {
    $lat  = (float)$lat; $lon = (float)$lon; $elv = (float)$elv;
    $km   = (float)$km;  $m   = (float)$m;
    $code = $conn->real_escape_string($code);
    $name = $conn->real_escape_string($name);
    $desc = $conn->real_escape_string($desc);
    $sql  = "INSERT INTO road_assets (region_id,latitude,longitude,elevation,road_code,road_name,length_km,width_m,description)
             VALUES ($rid,$lat,$lon,$elv,'$code','$name',$km,$m,'$desc')";
    if (!$conn->query($sql)) {
        $log[] = "⚠️ Error: " . $conn->error . " → $sql";
    }
}
$log[] = "✅ " . count($roads) . " aset jalan Ternate & Sofifi dimasukkan.";

// ── 6. Verifikasi ─────────────────────────────────────────────────────────
$rCount = $conn->query("SELECT COUNT(*) AS n FROM regions")->fetch_object()->n;
$aCount = $conn->query("SELECT COUNT(*) AS n FROM road_assets")->fetch_object()->n;
$log[] = "📊 regions: $rCount baris | road_assets: $aCount baris";

$conn->close();

// ── Output ─────────────────────────────────────────────────────────────────
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sismap Seeder</title>
    <style>
        body{font-family:monospace;background:#0f172a;color:#e2e8f0;padding:2rem}
        h2{color:#818cf8}
        .ok{color:#34d399}.warn{color:#fbbf24}.done{color:#818cf8;font-size:1.2em;margin-top:1rem}
        a{color:#818cf8}
    </style>
</head>
<body>
    <h2>🗂️ Sismap DB Seeder</h2>
    <?php foreach ($log as $l): ?>
    <p class="<?= str_contains($l,'⚠️') ? 'warn' : 'ok' ?>"><?= htmlspecialchars($l) ?></p>
    <?php endforeach; ?>
    <p class="done">✔ Selesai! <a href="/admin/jalan">→ Buka Halaman Jalan</a></p>
    <p style="color:#475569;font-size:.8em;margin-top:2rem">Hapus file ini setelah selesai: <code>public/seed.php</code></p>
</body>
</html>

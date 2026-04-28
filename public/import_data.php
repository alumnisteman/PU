<?php
/**
 * North Maluku Infrastructure Control Center - Data Importer
 * Akses: http://192.168.1.7:8008/import_data.php
 */

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

$csvData = <<<CSV
kode_ruas,nama_ruas,kota,kecamatan,panjang_km,lebar_m,kondisi,latitude,longitude
TTE-TS-001,Jalan Sultan Khairun,Ternate,Ternate Selatan,1.2,6,Baik,-0.7921,127.3845
TTE-TS-002,Jalan Bastiong Raya,Ternate,Ternate Selatan,0.9,5,Sedang,-0.7952,127.3811
TTE-TS-003,Jalan Mangga Dua,Ternate,Ternate Selatan,1.1,6,Baik,-0.7901,127.3841
TTE-TS-004,Jalan Kalumata,Ternate,Ternate Selatan,1.3,6,Sedang,-0.7933,127.3860
TTE-TS-005,Jalan Fitu,Ternate,Ternate Selatan,0.7,4,Rusak Ringan,-0.7960,127.3799
TTE-TT-006,Jalan Ahmad Yani,Ternate,Ternate Tengah,1.5,7,Baik,-0.7888,127.3819
TTE-TT-007,Jalan Merdeka,Ternate,Ternate Tengah,0.8,5,Rusak Ringan,-0.7895,127.3822
TTE-TT-008,Jalan Pahlawan Revolusi,Ternate,Ternate Tengah,1.0,6,Baik,-0.7871,127.3805
TTE-TT-009,Jalan Hasan Esa,Ternate,Ternate Tengah,1.2,6,Sedang,-0.7882,127.3830
TTE-TT-010,Jalan Jati,Ternate,Ternate Tengah,0.6,4,Rusak Berat,-0.7890,127.3795
TTE-TU-011,Jalan Dufa-Dufa,Ternate,Ternate Utara,1.7,6,Sedang,-0.7751,127.3702
TTE-TU-012,Jalan Sangaji,Ternate,Ternate Utara,1.1,5,Baik,-0.7763,127.3721
TTE-TU-013,Jalan Tafure,Ternate,Ternate Utara,0.9,5,Rusak Ringan,-0.7745,127.3688
TTE-TU-014,Jalan Akehuda,Ternate,Ternate Utara,1.4,6,Baik,-0.7739,127.3699
TTE-TU-015,Jalan Tubo,Ternate,Ternate Utara,0.8,4,Sedang,-0.7758,127.3710
TTE-PH-016,Jalan Hiri Selatan,Ternate,Pulau Hiri,1.4,4,Sedang,-0.7202,127.3002
TTE-PH-017,Jalan Hiri Utara,Ternate,Pulau Hiri,1.1,4,Baik,-0.7189,127.3015
TTE-PBD-018,Jalan Batang Dua Timur,Ternate,Pulau Batang Dua,2.3,4,Rusak Berat,-0.6501,127.2001
TTE-PBD-019,Jalan Batang Dua Barat,Ternate,Pulau Batang Dua,2.0,4,Sedang,-0.6520,127.1988
TTE-TS-020,Jalan Kayu Merah,Ternate,Ternate Selatan,1.0,5,Baik,-0.7912,127.3850
TTE-TS-021,Jalan Ngade,Ternate,Ternate Selatan,1.3,6,Sedang,-0.7940,127.3871
TTE-TT-022,Jalan Maliaro,Ternate,Ternate Tengah,0.9,5,Baik,-0.7875,127.3810
TTE-TT-023,Jalan Stadion,Ternate,Ternate Tengah,1.2,6,Sedang,-0.7881,127.3828
TTE-TU-024,Jalan Kastela,Ternate,Ternate Utara,1.6,6,Baik,-0.7765,127.3732
TTE-TU-025,Jalan Soa-Sio,Ternate,Ternate Utara,1.0,5,Rusak Ringan,-0.7740,127.3700
SOF-OB-026,Jalan Sofifi-Oba,Sofifi,Oba,3.5,7,Baik,0.6701,127.5601
SOF-OB-027,Jalan Oba Tengah,Sofifi,Oba Tengah,2.1,6,Sedang,0.6723,127.5632
SOF-OB-028,Jalan Oba Selatan,Sofifi,Oba Selatan,2.8,5,Rusak Ringan,0.6688,127.5581
SOF-OB-029,Jalan Oba Utara,Sofifi,Oba Utara,3.0,6,Baik,0.6751,127.5702
SOF-OB-030,Jalan Guraping,Sofifi,Oba Utara,1.9,5,Baik,0.6762,127.5711
SOF-OB-031,Jalan Balbar,Sofifi,Oba,2.5,6,Sedang,0.6699,127.5590
SOF-OB-032,Jalan Galala,Sofifi,Oba Selatan,1.8,5,Baik,0.6675,127.5567
SOF-OB-033,Jalan Kusu,Sofifi,Oba Tengah,2.0,6,Sedang,0.6712,127.5622
SOF-OB-034,Jalan Weda Link,Sofifi,Oba,3.2,7,Baik,0.6730,127.5650
SOF-OB-035,Jalan Sofifi Pelabuhan,Sofifi,Oba Utara,1.5,5,Rusak Ringan,0.6771,127.5720
SOF-OB-036,Jalan Trans Oba 1,Sofifi,Oba,2.0,6,Baik,0.6705,127.5610
SOF-OB-037,Jalan Trans Oba 2,Sofifi,Oba Tengah,2.2,6,Sedang,0.6710,127.5625
SOF-OB-038,Jalan Trans Oba 3,Sofifi,Oba Selatan,2.4,5,Rusak Ringan,0.6680,127.5570
SOF-OB-039,Jalan Trans Oba 4,Sofifi,Oba Utara,2.6,6,Baik,0.6755,127.5715
SOF-OB-040,Jalan Trans Oba 5,Sofifi,Oba,2.8,6,Sedang,0.6720,127.5635
TTE-AUTO-041,Jalan Lingkar Ternate 1,Ternate,Ternate Tengah,1.0,5,Baik,-0.7880,127.3820
TTE-AUTO-042,Jalan Lingkar Ternate 2,Ternate,Ternate Tengah,1.1,5,Sedang,-0.7885,127.3825
TTE-AUTO-043,Jalan Lingkar Ternate 3,Ternate,Ternate Selatan,1.2,6,Baik,-0.7910,127.3855
TTE-AUTO-044,Jalan Lingkar Ternate 4,Ternate,Ternate Selatan,1.3,6,Sedang,-0.7920,127.3865
TTE-AUTO-045,Jalan Lingkar Ternate 5,Ternate,Ternate Utara,1.4,6,Baik,-0.7755,127.3715
TTE-AUTO-046,Jalan Permukiman 1,Ternate,Ternate Selatan,0.5,4,Rusak Ringan,-0.7935,127.3870
TTE-AUTO-047,Jalan Permukiman 2,Ternate,Ternate Selatan,0.6,4,Baik,-0.7945,127.3880
TTE-AUTO-048,Jalan Permukiman 3,Ternate,Ternate Tengah,0.7,4,Sedang,-0.7870,127.3815
TTE-AUTO-049,Jalan Permukiman 4,Ternate,Ternate Utara,0.8,4,Baik,-0.7760,127.3725
TTE-AUTO-050,Jalan Permukiman 5,Ternate,Ternate Utara,0.9,4,Rusak Berat,-0.7770,127.3735
SOF-AUTO-051,Jalan Kawasan 1,Sofifi,Oba,1.0,5,Baik,0.6715,127.5620
SOF-AUTO-052,Jalan Kawasan 2,Sofifi,Oba Tengah,1.2,5,Sedang,0.6725,127.5630
SOF-AUTO-053,Jalan Kawasan 3,Sofifi,Oba Selatan,1.3,5,Rusak Ringan,0.6685,127.5575
SOF-AUTO-054,Jalan Kawasan 4,Sofifi,Oba Utara,1.4,6,Baik,0.6765,127.5725
SOF-AUTO-055,Jalan Kawasan 5,Sofifi,Oba,1.5,6,Sedang,0.6735,127.5640
CSV;

$lines = explode("\n", trim($csvData));
array_shift($lines); // Remove header

$log = [];

// 1. Clear existing data (Fresh Start)
$conn->query("SET FOREIGN_KEY_CHECKS=0");
$conn->query("TRUNCATE TABLE road_assets");
$conn->query("TRUNCATE TABLE regions");
$conn->query("SET FOREIGN_KEY_CHECKS=1");
$log[] = "✅ Database dibersihkan (road_assets & regions).";

// 2. Map regions and insert
$regionMap = []; // "city|district" => id
$roadCount = 0;

foreach ($lines as $line) {
    $row = str_getcsv($line);
    if (count($row) < 9) continue;
    
    $code      = $row[0];
    $name      = $row[1];
    $city      = $row[2];
    $district  = $row[3];
    $length_km = (float)$row[4];
    $width_m   = (float)$row[5];
    $condition = $row[6];
    $lat       = (float)$row[7];
    $lng       = (float)$row[8];
    
    $regionKey = "$city|$district";
    if (!isset($regionMap[$regionKey])) {
        $stmt = $conn->prepare("INSERT INTO regions (province, city, district) VALUES ('Maluku Utara', ?, ?)");
        $stmt->bind_param('ss', $city, $district);
        $stmt->execute();
        $regionMap[$regionKey] = $conn->insert_id;
        $stmt->close();
    }
    
    $regionId = $regionMap[$regionKey];
    
    // Map condition string to ENUM ('Baik','Rusak Ringan','Rusak Berat')
    // Sedang treated as 'Baik' or map to nearest?
    $finalCond = 'Baik';
    if (stripos($condition, 'Rusak Berat') !== false) $finalCond = 'Rusak Berat';
    elseif (stripos($condition, 'Rusak Ringan') !== false) $finalCond = 'Rusak Ringan';
    elseif (stripos($condition, 'Sedang') !== false) $finalCond = 'Rusak Ringan'; // Map Sedang to RR
    
    $stmt = $conn->prepare("INSERT INTO road_assets (region_id, latitude, longitude, elevation, road_code, road_name, length_km, width_m, description, condition_status) 
                            VALUES (?, ?, ?, 0, ?, ?, ?, ?, '', ?)");
    $stmt->bind_param('iddssdds', $regionId, $lat, $lng, $code, $name, $length_km, $width_m, $finalCond);
    $stmt->execute();
    $stmt->close();
    $roadCount++;
}

$log[] = "✅ " . count($regionMap) . " Wilayah (Kecamatan) berhasil didaftarkan.";
$log[] = "✅ $roadCount Ruas Jalan berhasil diimport.";

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Import Data North Maluku</title>
    <style>
        body{font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:2rem}
        h2{color:#38bdf8}
        .ok{color:#34d399;margin-bottom:.5rem}
        .done{background:#1e293b;padding:1.5rem;border-radius:1rem;border:1px solid #334155;margin-top:2rem}
        a{color:#38bdf8;text-decoration:none;font-weight:bold}
    </style>
</head>
<body>
    <h2>🚀 Import Data Infrastruktur Maluku Utara</h2>
    <div class="done">
        <?php foreach ($log as $l): ?>
        <div class="ok"><?= $l ?></div>
        <?php endforeach; ?>
        <hr style="border:0;border-top:1px solid #334155;margin:1.5rem 0">
        <p>✔ Impor Selesai! Data sekarang menggunakan database <b>sismap</b>.</p>
        <a href="/admin/jalan">→ Buka Dashboard Admin</a> | 
        <a href="/">→ Lihat Peta Pulse</a>
    </div>
</body>
</html>

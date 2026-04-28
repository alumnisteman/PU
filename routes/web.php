<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JalanController;
use App\Http\Controllers\JembatanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| HALAMAN PUBLIK - Pulse Dashboard (Informasi Jalan & Jembatan)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('admin.command-center');
})->name('public.home');

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/login',  [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| ADMIN PORTAL - Data Entry & Management
| Route names match existing view references (no admin. prefix needed)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware('auth')->group(function () {

    // Dashboard
    Route::get('/',         [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard',[DashboardController::class, 'index'])->name('dashboard');

    // Data Jalan - names kept as 'jalan.*' to match existing views
    Route::get('/jalan',            [JalanController::class, 'index'])->name('jalan.index');
    Route::get('/jalan/create',     [JalanController::class, 'create'])->name('jalan.create');
    Route::post('/jalan',           [JalanController::class, 'store'])->name('jalan.store');
    Route::get('/jalan/{id}',       [JalanController::class, 'show'])->name('jalan.show');
    Route::get('/jalan/{id}/edit',  [JalanController::class, 'edit'])->name('jalan.edit');
    Route::put('/jalan/{id}',       [JalanController::class, 'update'])->name('jalan.update');
    Route::delete('/jalan/{id}',    [JalanController::class, 'destroy'])->name('jalan.destroy');

    // Data Jembatan - names kept as 'jembatan.*' to match existing views
    Route::resource('jembatan', JembatanController::class);

    // Users & Gallery
    Route::get('/users',   [UserController::class, 'index'])->name('users.index');
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

    // Material Management
    Route::post('/material/update', [App\Http\Controllers\MaterialController::class, 'updatePrice'])->name('material.update');
    Route::post('/road-material/add', [App\Http\Controllers\MaterialController::class, 'addRoadMaterial'])->name('road_material.add');
    Route::delete('/road-material/{id}', [App\Http\Controllers\MaterialController::class, 'removeRoadMaterial'])->name('road_material.remove');

    // Pulse Command Center
    Route::get('/command-center', function () {
        return view('admin.command-center');
    })->name('admin.command-center');

    // Debug DB
    Route::get('/debug-db', function () {
        try {
            $regions   = DB::select('SELECT id, province, city, district FROM regions LIMIT 20');
            $assets    = DB::select('SELECT id, region_id, road_name, condition_status FROM road_assets LIMIT 20');
            $tbls      = DB::select('SHOW TABLES');
            return response()->json([
                'tables'      => $tbls,
                'regions'     => $regions,
                'road_assets' => $assets,
                'regions_count'     => DB::table('regions')->count(),
                'road_assets_count' => DB::table('road_assets')->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    });

    // Init Data
    Route::get('/init-data', function () {
        try {
            // Create regions table if it has different structure or doesn't exist
            // Actually, let's just run the user's SQL directly if possible.
            // But we should check if we need to DROP first.
            
            // The user's SQL:
            $sql_regions = "
                CREATE TABLE IF NOT EXISTS regions (
                  id INT AUTO_INCREMENT PRIMARY KEY,
                  province VARCHAR(100),
                  city VARCHAR(100),
                  district VARCHAR(100)
                );
            ";
            
            $sql_road_assets = "
                CREATE TABLE IF NOT EXISTS road_assets (
                  id INT AUTO_INCREMENT PRIMARY KEY,
                  region_id INT,
                  latitude DECIMAL(10,8),
                  longitude DECIMAL(11,8),
                  elevation DECIMAL(10,2),
                  road_code VARCHAR(50),
                  road_name VARCHAR(200),
                  length_km DECIMAL(10,2),
                  width_m DECIMAL(10,2),
                  description TEXT,
                  condition_status ENUM('Baik', 'Rusak Ringan', 'Rusak Berat') DEFAULT 'Baik',
                  FOREIGN KEY (region_id) REFERENCES regions(id)
                );
            ";

            Illuminate\Support\Facades\DB::statement($sql_regions);
            Illuminate\Support\Facades\DB::statement($sql_road_assets);

            // Insert regions
            Illuminate\Support\Facades\DB::table('regions')->truncate();
            Illuminate\Support\Facades\DB::table('regions')->insert([
                ['province' => 'Maluku Utara', 'city' => 'Ternate', 'district' => 'Ternate Tengah'],
                ['province' => 'Maluku Utara', 'city' => 'Ternate', 'district' => 'Ternate Tengah'],
                ['province' => 'Maluku Utara', 'city' => 'Ternate', 'district' => 'Ternate Tengah'],
                ['province' => 'Maluku Utara', 'city' => 'Ternate', 'district' => 'Ternate Utara'],
                ['province' => 'Maluku Utara', 'city' => 'Ternate', 'district' => 'Ternate Utara'],
                ['province' => 'Maluku Utara', 'city' => 'Ternate', 'district' => 'Ternate Utara'],
                ['province' => 'Maluku Utara', 'city' => 'Ternate', 'district' => 'Ternate Selatan'],
                ['province' => 'Maluku Utara', 'city' => 'Ternate', 'district' => 'Ternate Selatan'],
                ['province' => 'Maluku Utara', 'city' => 'Ternate', 'district' => 'Ternate Selatan'],
                ['province' => 'Maluku Utara', 'city' => 'Ternate', 'district' => 'Ternate Barat'],
                ['province' => 'Maluku Utara', 'city' => 'Ternate', 'district' => 'Ternate Barat'],
            ]);

            // Insert road assets
            Illuminate\Support\Facades\DB::table('road_assets')->truncate();
            Illuminate\Support\Facades\DB::table('road_assets')->insert([
                ['region_id' => 1, 'latitude' => -0.79012, 'longitude' => 127.38411, 'elevation' => 10, 'road_code' => 'TRT-TGH-001', 'road_name' => 'Jalan Sultan Khairun', 'length_km' => 2.1, 'width_m' => 6, 'description' => 'Jalan pusat kota'],
                ['region_id' => 2, 'latitude' => -0.78955, 'longitude' => 127.38222, 'elevation' => 12, 'road_code' => 'TRT-TGH-002', 'road_name' => 'Jalan Merdeka', 'length_km' => 1.5, 'width_m' => 5, 'description' => 'Dekat area pasar'],
                ['region_id' => 3, 'latitude' => -0.78890, 'longitude' => 127.38350, 'elevation' => 9, 'road_code' => 'TRT-TGH-003', 'road_name' => 'Jalan Pahlawan', 'length_km' => 1.2, 'width_m' => 4, 'description' => 'Akses sekolah'],
                ['region_id' => 4, 'latitude' => -0.75643, 'longitude' => 127.37219, 'elevation' => 11, 'road_code' => 'TRT-UTR-001', 'road_name' => 'Jalan Kasturian Raya', 'length_km' => 1.8, 'width_m' => 5.5, 'description' => 'Permukiman warga'],
                ['region_id' => 5, 'latitude' => -0.75210, 'longitude' => 127.37050, 'elevation' => 13, 'road_code' => 'TRT-UTR-002', 'road_name' => 'Jalan Dufa-Dufa', 'length_km' => 2.3, 'width_m' => 6, 'description' => 'Jalan penghubung'],
                ['region_id' => 6, 'latitude' => -0.75088, 'longitude' => 127.36900, 'elevation' => 14, 'road_code' => 'TRT-UTR-003', 'road_name' => 'Jalan Akehuda', 'length_km' => 1.9, 'width_m' => 5, 'description' => 'Akses nelayan'],
                ['region_id' => 7, 'latitude' => -0.80122, 'longitude' => 127.36590, 'elevation' => 8, 'road_code' => 'TRT-SLT-001', 'road_name' => 'Jalan Bastiong', 'length_km' => 2.8, 'width_m' => 7, 'description' => 'Jalan utama selatan'],
                ['region_id' => 8, 'latitude' => -0.80311, 'longitude' => 127.36740, 'elevation' => 7, 'road_code' => 'TRT-SLT-002', 'road_name' => 'Jalan Gambesi', 'length_km' => 2.0, 'width_m' => 6, 'description' => 'Dekat bandara'],
                ['region_id' => 9, 'latitude' => -0.80455, 'longitude' => 127.36990, 'elevation' => 9, 'road_code' => 'TRT-SLT-003', 'road_name' => 'Jalan Sasa', 'length_km' => 1.7, 'width_m' => 5, 'description' => 'Permukiman'],
                ['region_id' => 10, 'latitude' => -0.77012, 'longitude' => 127.35010, 'elevation' => 15, 'road_code' => 'TRT-BRT-001', 'road_name' => 'Jalan Sulamadaha', 'length_km' => 3.2, 'width_m' => 6, 'description' => 'Akses wisata'],
                ['region_id' => 11, 'latitude' => -0.76800, 'longitude' => 127.34800, 'elevation' => 16, 'road_code' => 'TRT-BRT-002', 'road_name' => 'Jalan Tobololo', 'length_km' => 2.4, 'width_m' => 5, 'description' => 'Jalan pesisir'],
            ]);

            return "Data initialized successfully.";
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    });
});

/*
|--------------------------------------------------------------------------
| REPORTS
|--------------------------------------------------------------------------
*/
Route::get('/reports/jalan/{id}', [ReportController::class, 'printJalan'])->name('reports.jalan');
Route::get('/reports/roads/pdf',  [ReportController::class, 'printRoads']);

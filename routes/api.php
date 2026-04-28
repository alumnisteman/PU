<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\RoadController;
use App\Http\Controllers\DamageReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/upload', [UploadController::class, 'store']);
    
    // Wilayah Legacy Routes
    Route::get('/wilayah/provinces', [WilayahController::class, 'provinces']);
    Route::get('/wilayah/cities/{provinceId}', [WilayahController::class, 'cities']);
    Route::get('/wilayah/districts/{cityId}', [WilayahController::class, 'districts']);
    Route::post('/wilayah', [WilayahController::class, 'store']);
});

Route::get('/roads/dashboard', [RoadController::class, 'dashboard']);
Route::post('/gps', [App\Http\Controllers\RoadController::class, 'updateGPS'])->middleware('auth:sanctum');

// Dashboard KPI Stats - langsung dari tabel roads (sumber SISMAP PULSE)
Route::get('/dashboard/stats', function() {
    try {
        $stats = \Illuminate\Support\Facades\DB::table('roads')
            ->select(\Illuminate\Support\Facades\DB::raw('`condition`, count(*) as total'))
            ->groupBy('condition')
            ->get()
            ->mapWithKeys(function ($item) {
                return [strtolower($item->condition) => $item->total];
            });

        return response()->json([
            'baik'         => $stats->get('baik') ?? 0,
            'sedang'       => $stats->get('sedang') ?? 0,
            'rusak_ringan' => $stats->get('rusak_ringan') ?? 0,
            'rusak_berat'  => $stats->get('rusak_berat') ?? 0,
            'total'        => \Illuminate\Support\Facades\DB::table('roads')->count(),
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Damage Reports & AI
Route::get('/reports', [App\Http\Controllers\DamageReportController::class, 'index']);
Route::post('/reports', [App\Http\Controllers\DamageReportController::class, 'store'])->middleware('auth:sanctum');

Route::get('/segments', function(Request $request){
    try {
        if (!$request->has('bbox')) return response()->json(['type'=>'FeatureCollection', 'features'=>[]]);
        $bboxStr = $request->get('bbox');
        $bbox = explode(',', $bboxStr);
        if (count($bbox) !== 4) return response()->json(['type'=>'FeatureCollection', 'features'=>[]]);
        
        return Illuminate\Support\Facades\Cache::remember("segments_bbox_{$bboxStr}", 60, function() use ($bbox, $request) {
            [$minLon, $minLat, $maxLon, $maxLat] = $bbox;
            $polygon = "POLYGON(($minLat $minLon, $minLat $maxLon, $maxLat $maxLon, $maxLat $minLon, $minLat $minLon))";

            $limitParam = $request->get('limit', 5000);
            $limit = min(5000, max(100, (int)$limitParam));

            // 1. Fetch assets into a fast lookup table
            $assets = \Illuminate\Support\Facades\DB::table('road_assets')->get();
            $assetMap = [];
            foreach ($assets as $asset) {
                $normalizedName = trim(strtolower($asset->road_name));
                $assetMap[$normalizedName] = $asset;
            }

            // 2. Fast spatial query on segments ONLY with prioritization
            $segments = Illuminate\Support\Facades\DB::select("
                SELECT id, name, highway, area, `condition`, score, length_m, ST_AsGeoJSON(geom) as geometry
                FROM road_segments
                WHERE ST_Intersects(geom, ST_GeomFromText(?, 4326))
                ORDER BY FIELD(highway, 'trunk', 'primary', 'secondary', 'tertiary', 'residential', 'unclassified')
                LIMIT {$limit}
            ", [$polygon]);

            // 3. Merge data in memory
            foreach ($segments as &$s) {
                $normalizedSegName = trim(strtolower($s->name ?? ''));
                if ($normalizedSegName && isset($assetMap[$normalizedSegName])) {
                    $asset = $assetMap[$normalizedSegName];
                    $s->condition = $asset->condition_status ?? $s->condition;
                    $s->asset_id = $asset->id;
                    $s->photo_url = $asset->photo_url;
                    $s->asset_created_at = $asset->created_at;
                } else {
                    $s->asset_id = null;
                    $s->photo_url = null;
                    $s->asset_created_at = null;
                }
            }

            return [
                "type" => "FeatureCollection",
                "features" => collect($segments)->map(function($s) {
                    $cond = strtolower($s->condition);
                    if ($cond === 'rusak') $cond = 'rusak_berat'; // Map to red color in frontend
                    
                    $photoPath = null;
                    if ($s->photo_url) {
                        $year = \Carbon\Carbon::parse($s->asset_created_at)->format('Y');
                        $photoPath = url("uploads/others/pu/{$year}/{$s->photo_url}");
                    }

                    return [
                        "type" => "Feature",
                        "id" => $s->id,
                        "properties" => [
                            "id" => $s->id,
                            "asset_id" => $s->asset_id,
                            "name" => $s->name,
                            "highway" => $s->highway,
                            "area" => $s->area,
                            "condition" => $cond,
                            "score" => $s->score,
                            "length_m" => $s->length_m,
                            "photo_url" => $photoPath
                        ],
                        "geometry" => json_decode($s->geometry)
                    ];
                })
            ];
        });
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("API Segments Error: " . $e->getMessage());
        return response()->json(['type'=>'FeatureCollection', 'features'=>[], 'error' => true]);
    }
});

Route::apiResource('roads', RoadController::class);
Route::patch('/roads/{id}/condition', [RoadController::class, 'updateCondition']);
Route::get('/regions', [RoadController::class, 'getRegions']);
Route::post('/road-assets', [RoadController::class, 'storeAsset']);

// Endpoint untuk update kondisi road_asset langsung dari map
Route::patch('/road-assets/{id}/condition', [RoadController::class, 'updateAssetCondition']);
Route::get('/road-assets/{id}', [RoadController::class, 'getAsset']);

// North Maluku Infrastructure Expansion (Reports & AI)
Route::post('/reports', [DamageReportController::class, 'store']);
Route::get('/reports', [DamageReportController::class, 'index']);
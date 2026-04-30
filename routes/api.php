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

Route::post('/roads/register', function(Request $request) {
    return \Illuminate\Support\Facades\DB::transaction(function() use ($request) {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'length_m' => 'nullable|numeric',
                'condition' => 'nullable|string'
            ]);

            $name = $validated['name'];
            $lat = $validated['lat'];
            $lng = $validated['lng'];
            $length_km = ($validated['length_m'] ?? 0) / 1000;
            $condition = $validated['condition'] ?? 'baik';
            $code = 'R-' . strtoupper(substr(uniqid(), -4));

            // 1. Create in road_assets
            $assetId = \Illuminate\Support\Facades\DB::table('road_assets')->insertGetId([
                'road_name' => $name,
                'latitude' => $lat,
                'longitude' => $lng,
                'length_km' => $length_km,
                'width_m' => 6.0, 
                'condition_status' => $condition,
                'road_code' => $code,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Create or Update in roads (SISMAP PULSE table)
            \Illuminate\Support\Facades\DB::table('roads')->updateOrInsert(
                ['id' => $assetId],
                [
                    'name' => $name,
                    'code' => $code,
                    'lat' => $lat,
                    'lng' => $lng,
                    'length_km' => $length_km,
                    'condition' => $condition,
                    'geometry' => json_encode(['type' => 'Point', 'coordinates' => [(float)$lng, (float)$lat]]),
                    'geom' => \Illuminate\Support\Facades\DB::raw("ST_SRID(ST_GeomFromText('POINT($lng $lat)'), 4326)"),
                    'updated_at' => now(),
                    'created_at' => \Illuminate\Support\Facades\DB::raw('COALESCE(created_at, NOW())')
                ]
            );

            // Dispatch AI Scoring to Queue (Redis)
            \App\Jobs\CalculateRoadPriorityScore::dispatch($assetId);

            return response()->json([
                'success' => true,
                'asset_id' => $assetId,
                'message' => 'Jalan berhasil didaftarkan ke Admin'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });
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

            // 1b. Fetch main roads into a lookup table (Source of Truth for Condition)
            $roads = \Illuminate\Support\Facades\DB::table('roads')->get();
            $roadMap = [];
            foreach ($roads as $road) {
                $normalizedName = trim(strtolower($road->name));
                $roadMap[$normalizedName] = $road;
            }

            // 2. Fast spatial query on segments with administrative join
            $segments = \Illuminate\Support\Facades\DB::select("
                SELECT rs.id, rs.name, rs.highway, 
                       (SELECT nama_kelurahan FROM wil_kelurahan_tbl WHERE ST_Intersects(geom, rs.geom) LIMIT 1) as area,
                       rs.village_code as address, 
                       rs.condition, rs.score, rs.length_m, ST_AsGeoJSON(rs.geom) as geometry
                FROM road_segments rs
                WHERE ST_Intersects(rs.geom, ST_GeomFromText(?, 4326))
                ORDER BY FIELD(rs.highway, 'trunk', 'primary', 'secondary', 'tertiary', 'residential', 'unclassified')
                LIMIT {$limit}
            ", [$polygon]);

            // 3. Merge data in memory
            foreach ($segments as &$s) {
                $normalizedSegName = trim(strtolower($s->name ?? ''));
                $cleanSegName = preg_replace('/^(jalan|jl|jln)\.?\s+/i', '', $normalizedSegName);
                
                // Set defaults
                $s->asset_id = null;
                $s->photo_url = null;
                $s->asset_created_at = null;
                
                // Try to find a match in roads or assets
                $foundCondition = null;
                $foundAsset = null;

                // Priority 1: Exact Match in roadMap
                if ($normalizedSegName && isset($roadMap[$normalizedSegName])) {
                    $foundCondition = $roadMap[$normalizedSegName]->condition;
                }
                // Priority 2: Lenient Match in roadMap
                if (!$foundCondition && $cleanSegName) {
                    foreach($roadMap as $name => $r) {
                        $cleanRName = preg_replace('/^(jalan|jl|jln)\.?\s+/i', '', $name);
                        if ($cleanRName === $cleanSegName) {
                            $foundCondition = $r->condition;
                            break;
                        }
                    }
                }

                // Match with assets (for photos/IDs and fallback condition)
                if ($normalizedSegName && isset($assetMap[$normalizedSegName])) {
                    $foundAsset = $assetMap[$normalizedSegName];
                }
                if (!$foundAsset && $cleanSegName) {
                    foreach($assetMap as $name => $a) {
                        $cleanAName = preg_replace('/^(jalan|jl|jln)\.?\s+/i', '', $name);
                        if ($cleanAName === $cleanSegName) {
                            $foundAsset = $a;
                            break;
                        }
                    }
                }

                if ($foundCondition) $s->condition = $foundCondition;
                if ($foundAsset) {
                    if (!$foundCondition) $s->condition = $foundAsset->condition_status;
                    $s->asset_id = $foundAsset->id;
                    $s->photo_url = $foundAsset->photo_url;
                    $s->asset_created_at = $foundAsset->created_at;
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
                            "address" => $s->address,
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
Route::get('/roads/heatmap', [RoadController::class, 'getHeatmapData']);
Route::post('/snap', [RoadController::class, 'snapToRoad']);

// North Maluku Infrastructure Expansion (Reports & AI)
Route::post('/reports', [DamageReportController::class, 'store']);
Route::get('/reports', [DamageReportController::class, 'index']);

// Live Worker Tracking
Route::post('/worker/update-location', function(Request $req) {
    // Spatial lookup for village name to ensure precision
    $village = \Illuminate\Support\Facades\DB::selectOne("
        SELECT nama_kelurahan FROM wil_kelurahan_tbl 
        WHERE ST_Intersects(geom, ST_SetSRID(ST_Point(?, ?), 4326))
        LIMIT 1
    ", [$req->lng, $req->lat]);

    $villageName = $village->nama_kelurahan ?? 'Maluku Utara';

    // Upsert worker
    $worker = \App\Models\Worker::updateOrCreate(
        ['id' => $req->id],
        [
            'name' => $req->name ?? 'Petugas Lapangan ' . $req->id, 
            'lat' => $req->lat, 
            'lng' => $req->lng,
            'village' => $villageName // Store the precise village
        ]
    );

    broadcast(new \App\Events\WorkerLocationUpdated($worker));

    return response()->json(['ok' => true, 'village' => $villageName]);
});
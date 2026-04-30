<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Road;
use App\Models\RoadAsset;
use App\Models\DamageReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RoadController extends Controller
{
    public function index()
    {
        return Road::all();
    }

    public function store(Request $request)
    {
        $coords = $request->coords; // Expected array of [lng, lat]
        
        // 1. SNAP to nearest road geometry
        $snapped = $this->snap($coords);
        
        // 1b. REVERSE GEOCODE to get valid names
        $firstPoint = $snapped[0] ?? $coords[0];
        $geo = $this->reverseGeocode($firstPoint[1], $firstPoint[0]);

        // 2. SAVE Road data
        $road = Road::create([
            'name' => $request->nama ?? $request->name ?? $geo['road_name'],
            'condition' => $request->kondisi ?? $request->condition ?? 'baik',
            'length_km' => $request->length_km ?? 1.0,
            'region_id' => $request->region_id ?? 1,
            'geometry' => [
                'type' => 'LineString',
                'coordinates' => $snapped
            ]
        ]);

        // 3. AI PREDICTION for Priority Score
        $road->condition_score = $this->predict($road);
        $road->save();

        return response()->json($road, 201);
    }

    public function show($id)
    {
        return Road::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $road = Road::findOrFail($id);
        $road->update($request->all());
        return response()->json($road);
    }

    public function destroy($id)
    {
        Road::destroy($id);
        return response()->json(null, 204);
    }

    public function updateGPS(Request $request)
    {
        $request->user()->update([
            "lat" => $request->lat,
            "lng" => $request->lng,
            "accuracy" => $request->accuracy
        ]);
        return ["status" => "ok"];
    }

    public function dashboard(Request $request)
    {
        $searchQuery = $request->q;
        $cacheKey = 'dashboard_data_' . ($searchQuery ?: 'none');

        // Cache heavy stats and priority roads for 60 seconds
        $cachedData = Cache::remember($cacheKey, 30, function() use ($searchQuery) {
            // 1. Statistics from roads (THE SOURCE OF TRUTH)
            $assetStats = DB::table('roads')
                ->select('condition', DB::raw('count(*) as count'))
                ->groupBy('condition')
                ->get();

            $stats = [
                'baik' => 0, 'sedang' => 0, 'rusak_ringan' => 0, 'rusak_berat' => 0, 'total_km' => 0, 'total_ruas' => 0
            ];

            foreach ($assetStats as $s) {
                $cond = strtolower($s->condition);
                if (isset($stats[$cond])) {
                    $stats[$cond] = $s->count;
                }
            }
            
            $stats['total_ruas'] = DB::table('roads')->count();
            $stats['total_km'] = $stats['total_ruas'] * 1.2; // Estimasi atau ambil dari length jika ada
            $stats['rusak'] = $stats['rusak_ringan'] + $stats['rusak_berat'];

            // 2. Priority Roads from road_assets
            $roadsData = collect();
            if (!empty($searchQuery)) {
                $roads = Road::where('name', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('code', 'LIKE', "%{$searchQuery}%")
                    ->orderBy('name')
                    ->limit(20)
                    ->get();
                $roadsData = $roads->map(fn($r) => [
                    'id' => $r->id, 
                    'name' => $r->name, 
                    'code' => $r->code, 
                    'condition' => $r->condition, 
                    'priority_score' => $r->priority_score,
                    'lat' => $r->lat,
                    'lng' => $r->lng
                ]);
            } else {
                // 2. Priority Roads from road_assets with dynamic scoring & grouping
            $roadsData = DB::table('road_assets')
                ->select(
                    'road_name', 
                    DB::raw('MAX(road_code) as road_code'),
                    DB::raw('MAX(condition_status) as condition_status'),
                    DB::raw('SUM(length_km) as total_length'),
                    DB::raw('AVG(COALESCE(NULLIF(width_m, 0), 6)) as avg_width'),
                    DB::raw('MAX(latitude) as lat'),
                    DB::raw('MAX(longitude) as lng'),
                    DB::raw('
                        MAX(CASE 
                            WHEN LOWER(condition_status) = "rusak_berat" THEN 90
                            WHEN LOWER(condition_status) = "rusak_ringan" THEN 70
                            WHEN LOWER(condition_status) = "rusak" THEN 80
                            WHEN LOWER(condition_status) = "sedang" THEN 40
                            ELSE 10 
                        END) as priority_score
                    ')
                )
                ->groupBy('road_name')
                ->orderByDesc('priority_score')
                ->limit(100)
                ->get()
                ->map(fn($r) => [
                    'id' => $r->road_name, // Use name as ID for grouping
                    'name' => $r->road_name, 
                    'code' => $r->road_code, 
                    'condition' => strtolower($r->condition_status), 
                    'priority_score' => $r->priority_score,
                    'estimated_budget' => round($r->total_length * $r->avg_width * 1000 * 250000, 0),
                    'lat' => $r->lat,
                    'lng' => $r->lng
                ]);
            }

            // 3. Village Stats from road_assets joined with regions
            $villageStats = DB::table('road_assets')
                ->join('regions', 'road_assets.region_id', '=', 'regions.id')
                ->select('regions.district as name', 
                         DB::raw('SUM(length_km) as total_km'), 
                         DB::raw('SUM(CASE WHEN condition_status = "rusak" THEN length_km ELSE 0 END) as rusak_km'))
                ->groupBy('regions.district')
                ->orderByDesc('rusak_km')
                ->limit(5)
                ->get()
                ->map(fn($v) => [
                    'name' => $v->name,
                    'total_km' => round($v->total_km, 2),
                    'rusak_km' => round($v->rusak_km, 2),
                    'percent' => $v->total_km > 0 ? round(($v->rusak_km / $v->total_km) * 100, 1) : 0
                ]);

            return [
                'total_km'        => $stats['total_km'],
                'total_ruas'      => $stats['total_ruas'],
                'condition_stats' => $stats,
                'priority_roads'  => $roadsData->values(),
                'village_stats'   => $villageStats,
                'damaged_roads'   => DB::table('road_assets')
                    ->whereIn(DB::raw('LOWER(condition_status)'), ['rusak', 'rusak_ringan', 'rusak_berat'])
                    ->select('id', 'road_name as name', 'latitude as lat', 'longitude as lng', 'condition_status as condition')
                    ->get(),
                'damage_reports' => (!empty($searchQuery)) ? $roadsData->values() : DamageReport::with(['photos.aiAnalysis', 'roadAsset'])
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(fn($r) => [
                        'id' => $r->id,
                        'name' => $r->roadAsset->road_name ?? 'Unknown',
                        'code' => $r->roadAsset->road_code ?? 'N/A',
                        'condition' => $r->severity,
                        'damage_type' => $r->photos->first()?->aiAnalysis?->damage_type ?? 'Awaiting AI...',
                        'damage_details' => [
                            'confidence' => ($r->photos->first()?->aiAnalysis?->confidence ?? 0) . '%',
                            'severity_score' => $r->photos->first()?->aiAnalysis?->severity_score ?? 0,
                            'status' => $r->status
                        ]
                    ]),
                'ai_stats' => DB::table('ai_analysis')
                    ->select('damage_type', DB::raw('COUNT(*) as count'))
                    ->groupBy('damage_type')
                    ->pluck('count', 'damage_type')
            ];
        });

        // Merge with non-cached real-time data
        $data = array_merge($cachedData, [
            'roads'           => [], // Segments are loaded via /api/segments
            'users'           => User::whereNotNull('lat')->get(['user_id as id', 'user_name as name', 'lat', 'lng', 'accuracy']),
            'damage_reports'  => [], 
            'search_results'  => !empty($searchQuery)
        ]);

        return response()->json($data);
    }

    public function updateCondition(Request $request, $id)
    {
        $request->validate([
            'condition' => 'required|in:baik,sedang,rusak_ringan,rusak_berat'
        ]);

        // Check if it's a segment
        $segment = DB::table('road_segments')->where('id', $id)->first();
        if ($segment) {
            $oldCondition = $segment->condition;
            DB::table('road_segments')->where('id', $id)->update(['condition' => $request->condition]);
            
            DB::table('road_logs')->insert([
                'road_id' => $id,
                'old_condition' => $oldCondition,
                'new_condition' => $request->condition,
                'length_km' => ($segment->length_m ?? 80) / 1000,
                'created_at' => now()
            ]);

            $this->broadcastUpdate($id, $request->condition);

            return response()->json(['success' => true]);
        }

        // Legacy road support
        $road = Road::find($id);
        if ($road) {
            $oldCondition = $road->condition;
            $road->condition = $request->condition;
            $road->save();

            DB::table('road_logs')->insert([
                'road_id' => $road->id,
                'old_condition' => $oldCondition,
                'new_condition' => $road->condition,
                'length_km' => $road->length_km,
                'created_at' => now()
            ]);

            $this->broadcastUpdate($id, $road->condition);

            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Road not found'], 404);
    }

    private function broadcastUpdate($roadId, $condition, array $extra = [])
    {
        try {
            // Clear relevant caches
            Cache::flush(); 
            
            // Trigger Node.js broadcast
            \Illuminate\Support\Facades\Http::timeout(2)->post('http://127.0.0.1:3000/broadcast', [
                'event' => 'data-refresh',
                'data' => array_merge([
                    'id'        => $roadId, 
                    'condition' => $condition,
                    'message'   => 'Update kondisi jalan detect!'
                ], $extra)
            ]);
        } catch (\Exception $e) {
            // Silently fail if node server is down
        }
    }

    public function storeAsset(Request $request)
    {
        $validated = $request->validate([
            'region_id'    => 'required|exists:regions,id',
            'latitude'     => 'required|numeric',
            'longitude'    => 'required|numeric',
            'road_code'    => 'required|unique:road_assets,road_code',
            'road_name'    => 'required|string|max:255',
            'length_km'    => 'required|numeric',
            'width_m'      => 'required|numeric',
            'description'  => 'nullable|string',
            'photo_url'    => 'nullable|string',
            'condition_status' => 'required|in:baik,sedang,rusak'
        ]);

        DB::table('road_assets')->insert($validated);

        return response()->json([
            'success' => true,
            'message' => 'Aset jalan berhasil ditambahkan'
        ]);
    }

    public function getRegions()
    {
        return DB::table('regions')->get();
    }

    /**
     * Update kondisi road_asset langsung dari map popup.
     * POST dari MapView.vue → update DB → broadcast ke semua dashboard.
     */
    public function updateAssetCondition(Request $request, $id)
    {
        $request->validate([
            'condition' => 'required|in:baik,sedang,rusak_ringan,rusak_berat,rusak'
        ]);

        $asset = DB::table('road_assets')->where('id', $id)->first();
        if (!$asset) {
            return response()->json(['error' => 'Asset tidak ditemukan'], 404);
        }

        $oldCondition = $asset->condition_status;
        $newCondition = $request->condition;

        // Normalise: rusak_berat / rusak_ringan → simpan sebagai rusak di road_assets
        // tapi juga simpan nilai aslinya di road_segments jika ada
        $saveCondition = in_array($newCondition, ['rusak_berat', 'rusak_ringan']) ? $newCondition : $newCondition;

        DB::table('road_assets')->where('id', $id)->update([
            'condition_status' => $saveCondition
        ]);

        // Update juga road_segments yang nama-nya sama
        if ($asset->road_name) {
            DB::table('road_segments')
                ->where('name', $asset->road_name)
                ->update(['condition' => $newCondition]);
        }

        // Log perubahan
        try {
            DB::table('road_logs')->insert([
                'road_id'       => $id,
                'old_condition' => $oldCondition,
                'new_condition' => $newCondition,
                'length_km'     => $asset->length_km ?? 0,
                'created_at'    => now(),
            ]);
        } catch (\Exception $e) { /* tabel mungkin belum ada */ }

        // Flush cache
        Cache::flush();

        // Broadcast ke semua dashboard
        $this->broadcastUpdate($id, $newCondition, [
            'asset_id' => $id,
            'name'     => $asset->road_name,
            'lat'      => $asset->latitude,
            'lng'      => $asset->longitude,
        ]);

        return response()->json([
            'success'       => true,
            'id'            => $id,
            'old_condition' => $oldCondition,
            'new_condition' => $newCondition,
        ]);
    }

    /**
     * Ambil detail satu road asset untuk popup di map.
     */
    public function getAsset($id)
    {
        $asset = DB::table('road_assets')
            ->join('regions', 'road_assets.region_id', '=', 'regions.id')
            ->select(
                'road_assets.id', 'road_assets.road_name as name', 'road_assets.road_code as code',
                'road_assets.condition_status as condition', 'road_assets.length_km',
                'road_assets.width_m', 'road_assets.latitude', 'road_assets.longitude',
                'road_assets.photo_url', 'road_assets.description', 'road_assets.created_at',
                'regions.district', 'regions.city', 'regions.province'
            )
            ->where('road_assets.id', $id)
            ->first();

        if (!$asset) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json($asset);
    }

    /**
     * Snap coordinates to the nearest road geometry using OSRM API.
     */
    public function snapToRoad(Request $request)
    {
        $coords = $request->input('coords'); // Array of [lng, lat]
        return response()->json($this->snap($coords));
    }

    /**
     * Internal snap logic helper
     */
    private function snap($coords)
    {
        if (!$coords || count($coords) < 1) return $coords;

        $coordString = collect($coords)
            ->map(fn($c) => $c[0].','.$c[1])
            ->implode(';');

        try {
            $osrmUrl = env('OSRM_URL', 'http://osrm:5000');
            $url = "{$osrmUrl}/match/v1/driving/{$coordString}?geometries=geojson&overview=full";
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($url);

            if ($response->successful() && isset($response->json()['matchings'][0])) {
                // Returns [lng, lat] to maintain geojson compatibility
                return $response->json()['matchings'][0]['geometry']['coordinates'];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("OSRM Snap Error: " . $e->getMessage());
        }

        return $coords;
    }

    /**
     * Call AI Service to predict road risk/priority score.
     */
    public function predict(Road $road)
    {
        try {
            $aiUrl = env('AI_URL', 'http://ai:8000');
            $res = \Illuminate\Support\Facades\Http::timeout(5)->post($aiUrl . '/predict', [
                "kondisi"  => $this->mapConditionToScore($road->condition),
                "traffic"  => 70, // Default mock values if not available
                "rainfall" => 60,
                "age"      => 5,
                "reports"  => $road->damageReports()->count(),
                "length"   => $road->length_km
            ]);

            return $res->json()['risk_score'] ?? 0;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("AI Prediction Error: " . $e->getMessage());
            return 0;
        }
    }

    private function mapConditionToScore($condition)
    {
        $map = ['baik' => 10, 'sedang' => 40, 'rusak_ringan' => 70, 'rusak_berat' => 90];
        return $map[strtolower($condition)] ?? 50;
    }

    /**
     * Reverse Geocode coordinates to get valid Road Name and Village using PostGIS.
     */
    private function reverseGeocode($lat, $lng)
    {
        try {
            // 1. Find nearest road segment name
            $road = DB::selectOne("
                SELECT name FROM road_segments 
                ORDER BY geom <-> ST_SetSRID(ST_Point(?, ?), 4326) 
                LIMIT 1
            ", [$lng, $lat]);

            // 2. Find village (Kelurahan) name using spatial intersect + distance fallback for precision
            $village = DB::selectOne("
                SELECT nama_kelurahan FROM wil_kelurahan_tbl 
                ORDER BY 
                    ST_Intersects(geom, ST_SetSRID(ST_Point(?, ?), 4326)) DESC,
                    geom <-> ST_SetSRID(ST_Point(?, ?), 4326) ASC
                LIMIT 1
            ", [$lng, $lat, $lng, $lat]);

            $vName = $village->nama_kelurahan ?? 'Ternate';
            
            // Manual calibration for critical user-reported areas
            if (in_array(strtolower($vName), ['ternate', 'unknown', ''])) {
                if ($lat > -0.795 && $lat < -0.790 && $lng > 127.385) $vName = 'Salero';
                if ($lat > -0.800 && $lat < -0.795 && $lng > 127.380) $vName = 'Kasturian';
                if ($lat > -0.792 && $lat < -0.788 && $lng > 127.382) $vName = 'Soa';
                if ($lat > -0.787 && $lat < -0.782 && $lng > 127.380) $vName = 'Soasio';
            }

            return [
                'road_name' => $road->name ?? 'Jalan Lokal',
                'village'   => $vName
            ];
        } catch (\Exception $e) {
            return ['road_name' => 'Jalan Baru', 'village' => 'Maluku Utara'];
        }
    }

    /**
     * Data Heatmap untuk peta kepadatan kerusakan.
     */
    public function getHeatmapData()
    {
        return Cache::remember('heatmap_data', 60, function() {
            return DB::table('road_assets')
                ->whereIn(DB::raw('LOWER(condition_status)'), ['rusak', 'rusak_ringan', 'rusak_berat'])
                ->select('latitude as lat', 'longitude as lng', DB::raw('1 as intensity'))
                ->get();
        });
    }
}

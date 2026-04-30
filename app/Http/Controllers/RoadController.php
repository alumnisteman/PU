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

        try {
            // 1. Stats from road_assets
            $assetStats = DB::table('road_assets')
                ->select('condition_status as cond', DB::raw('count(*) as count'))
                ->groupBy('condition_status')
                ->get();

            $stats = ['baik' => 0, 'sedang' => 0, 'rusak_ringan' => 0, 'rusak_berat' => 0, 'total_km' => 0, 'total_ruas' => 0];
            foreach ($assetStats as $s) {
                $c = strtolower($s->cond);
                if (isset($stats[$c])) $stats[$c] = $s->count;
            }
            $stats['total_ruas'] = DB::table('road_assets')->count();
            $stats['total_km'] = DB::table('road_assets')->sum('length_km');
            $stats['rusak'] = ($stats['rusak_ringan'] ?? 0) + ($stats['rusak_berat'] ?? 0);

            // 2. Priority Roads with Geometry
            $query = DB::table('road_assets')
                ->leftJoin('roads', 'road_assets.road_name', '=', 'roads.name')
                ->select(
                    'road_assets.road_name as asset_name', 
                    DB::raw('MAX(road_assets.road_code) as road_code'),
                    DB::raw('MAX(road_assets.condition_status) as condition_status'),
                    DB::raw('SUM(road_assets.length_km) as total_length'),
                    DB::raw('AVG(COALESCE(NULLIF(road_assets.width_m, 0), 6)) as avg_width'),
                    DB::raw('MAX(road_assets.latitude) as lat'),
                    DB::raw('MAX(road_assets.longitude) as lng'),
                    DB::raw('MAX(roads.geometry) as geometry'),
                    DB::raw('
                        MAX(CASE 
                            WHEN LOWER(road_assets.condition_status) = "rusak_berat" THEN 90
                            WHEN LOWER(road_assets.condition_status) = "rusak_ringan" THEN 70
                            WHEN LOWER(road_assets.condition_status) = "rusak" THEN 80
                            WHEN LOWER(road_assets.condition_status) = "sedang" THEN 40
                            ELSE 10 
                        END) as priority_score
                    ')
                )
                ->groupBy('road_assets.road_name');

            if (!empty($searchQuery)) {
                $query->where('road_assets.road_name', 'LIKE', "%{$searchQuery}%");
            }

            $roadsData = $query->orderByDesc('priority_score')
                ->limit(100)
                ->get()
                ->map(fn($r) => [
                    'id' => $r->asset_name,
                    'name' => $r->asset_name, 
                    'code' => $r->road_code, 
                    'condition' => strtolower($r->condition_status), 
                    'priority_score' => $r->priority_score,
                    'estimated_budget' => round($r->total_length * $r->avg_width * 1000 * 250000, 0),
                    'lat' => $r->lat,
                    'lng' => $r->lng,
                    'geometry' => $r->geometry ? json_decode($r->geometry) : null
                ]);

            // 3. Village Stats (With Fallback)
            $villageStats = [];
            try {
                $villageStats = DB::table('road_assets')
                    ->leftJoin('regions', 'road_assets.region_id', '=', 'regions.id')
                    ->select('regions.district as name', 
                             DB::raw('SUM(road_assets.length_km) as total_km'), 
                             DB::raw('SUM(CASE WHEN road_assets.condition_status LIKE "rusak%" THEN road_assets.length_km ELSE 0 END) as rusak_km'))
                    ->groupBy('regions.district')
                    ->orderByDesc('rusak_km')
                    ->limit(5)
                    ->get()
                    ->map(fn($v) => [
                        'name' => $v->name ?? 'Luar Wilayah',
                        'total_km' => round($v->total_km, 2),
                        'rusak_km' => round($v->rusak_km, 2),
                        'percent' => $v->total_km > 0 ? round(($v->rusak_km / $v->total_km) * 100, 1) : 0
                    ]);
            } catch (\Exception $e) {}

                return response()->json([
                    'total_km'        => round($stats['total_km'], 2),
                    'total_ruas'      => $stats['total_ruas'],
                    'condition_stats' => $stats,
                    'priority_roads'  => $roadsData->values(),
                    'all_roads'       => DB::table('roads')->select('name', 'geometry', 'condition')->get()->map(fn($r) => [
                        'name' => $r->name,
                        'condition' => $r->condition,
                        'geometry' => json_decode($r->geometry)
                    ]),
                    'village_stats'   => $villageStats,
                    'damaged_roads'   => [],
                    'damage_reports'  => [],
                    'ai_stats'        => [],
                    'users'           => User::whereNotNull('lat')->get(['user_id as id', 'user_name as name', 'lat', 'lng', 'accuracy'])
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'condition_stats' => ['baik' => 0, 'sedang' => 0, 'rusak_ringan' => 0, 'rusak_berat' => 0],
                'priority_roads' => [],
                'village_stats' => [],
                'total_km' => 0,
                'total_ruas' => 0
            ]);
        }
    }

    public function updateCondition(Request $request, $id)
    {
        $request->validate([
            'condition' => 'required|in:baik,sedang,rusak_ringan,rusak_berat'
        ]);

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
            Cache::flush(); 
            \Illuminate\Support\Facades\Http::timeout(2)->post('http://127.0.0.1:3000/broadcast', [
                'event' => 'data-refresh',
                'data' => array_merge([
                    'id'        => $roadId, 
                    'condition' => $condition,
                    'message'   => 'Update kondisi jalan detect!'
                ], $extra)
            ]);
        } catch (\Exception $e) { }
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
        return response()->json(['success' => true, 'message' => 'Aset jalan berhasil ditambahkan']);
    }

    public function getRegions()
    {
        return DB::table('regions')->get();
    }

    public function updateAssetCondition(Request $request, $id)
    {
        $request->validate(['condition' => 'required|in:baik,sedang,rusak_ringan,rusak_berat,rusak']);

        $asset = DB::table('road_assets')->where('id', $id)->first();
        if (!$asset) return response()->json(['error' => 'Asset tidak ditemukan'], 404);

        $oldCondition = $asset->condition_status;
        $newCondition = $request->condition;

        DB::table('road_assets')->where('id', $id)->update(['condition_status' => $newCondition]);

        if ($asset->road_name) {
            DB::table('road_segments')->where('name', $asset->road_name)->update(['condition' => $newCondition]);
        }

        try {
            DB::table('road_logs')->insert([
                'road_id'       => $id,
                'old_condition' => $oldCondition,
                'new_condition' => $newCondition,
                'length_km'     => $asset->length_km ?? 0,
                'created_at'    => now(),
            ]);
        } catch (\Exception $e) { }

        Cache::flush();
        $this->broadcastUpdate($id, $newCondition, [
            'asset_id' => $id,
            'name'     => $asset->road_name,
            'lat'      => $asset->latitude,
            'lng'      => $asset->longitude,
        ]);

        return response()->json(['success' => true]);
    }

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

        if (!$asset) return response()->json(['error' => 'Not found'], 404);
        return response()->json($asset);
    }

    public function snapToRoad(Request $request)
    {
        $coords = $request->input('coords');
        return response()->json($this->snap($coords));
    }

    private function snap($coords)
    {
        if (!$coords || count($coords) < 1) return $coords;
        $coordString = collect($coords)->map(fn($c) => $c[0].','.$c[1])->implode(';');

        try {
            $osrmUrl = env('OSRM_URL', 'http://osrm:5000');
            $url = "{$osrmUrl}/match/v1/driving/{$coordString}?geometries=geojson&overview=full";
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($url);
            if ($response->successful() && isset($response->json()['matchings'][0])) {
                return $response->json()['matchings'][0]['geometry']['coordinates'];
            }
        } catch (\Exception $e) { }
        return $coords;
    }

    public function predict(Road $road)
    {
        try {
            $aiUrl = env('AI_URL', 'http://ai:8000');
            $res = \Illuminate\Support\Facades\Http::timeout(5)->post($aiUrl . '/predict', [
                "kondisi"  => $this->mapConditionToScore($road->condition),
                "traffic"  => 70,
                "rainfall" => 60,
                "age"      => 5,
                "reports"  => $road->damageReports()->count(),
                "length"   => $road->length_km
            ]);
            return $res->json()['risk_score'] ?? 0;
        } catch (\Exception $e) { return 0; }
    }

    private function mapConditionToScore($condition)
    {
        $map = ['baik' => 10, 'sedang' => 40, 'rusak_ringan' => 70, 'rusak_berat' => 90];
        return $map[strtolower($condition)] ?? 50;
    }

    private function reverseGeocode($lat, $lng)
    {
        try {
            $road = DB::selectOne("SELECT name FROM road_segments ORDER BY geom <-> ST_SetSRID(ST_Point(?, ?), 4326) LIMIT 1", [$lng, $lat]);
            $village = DB::selectOne("SELECT nama_kelurahan FROM wil_kelurahan_tbl ORDER BY ST_Intersects(geom, ST_SetSRID(ST_Point(?, ?), 4326)) DESC, geom <-> ST_SetSRID(ST_Point(?, ?), 4326) ASC LIMIT 1", [$lng, $lat, $lng, $lat]);
            return ['road_name' => $road->name ?? 'Jalan Lokal', 'village' => $village->nama_kelurahan ?? 'Ternate'];
        } catch (\Exception $e) {
            return ['road_name' => 'Jalan Baru', 'village' => 'Maluku Utara'];
        }
    }

    public function getHeatmapData()
    {
        return Cache::remember('heatmap_data', 60, function() {
            return DB::table('road_assets')->where('condition_status', 'LIKE', 'rusak%')->select('latitude as lat', 'longitude as lng', DB::raw('1 as intensity'))->get();
        });
    }
}

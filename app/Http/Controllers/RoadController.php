<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Road;
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
        $road = Road::create($request->all());
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
        $cachedData = Cache::remember($cacheKey, 60, function() use ($searchQuery) {
            // 1. Statistics from road_assets (THE SOURCE OF TRUTH)
            $assetStats = DB::table('road_assets')
                ->select('condition_status', DB::raw('SUM(length_km) as total_km'), DB::raw('COUNT(*) as count'))
                ->groupBy('condition_status')
                ->get();

            $stats = [
                'baik' => 0, 'sedang' => 0, 'rusak' => 0, 'rusak_ringan' => 0, 'rusak_berat' => 0, 'total_km' => 0, 'total_ruas' => 0
            ];

            foreach ($assetStats as $s) {
                $cond = strtolower($s->condition_status);
                $stats[$cond] = $s->count;
                $stats['total_km'] += $s->total_km;
            }
            
            // Handle categories for backward compatibility with frontend
            $stats['rusak_berat'] = $stats['rusak'];
            $stats['rusak'] = $stats['rusak'] + $stats['rusak_ringan']; 
            
            $stats['total_km'] = round($stats['total_km'], 2);
            $stats['total_ruas'] = DB::table('road_assets')->count();

            // 2. Priority Roads from road_assets
            $roadsData = collect();
            if (!empty($searchQuery)) {
                $roads = Road::search($searchQuery)->get();
                $roadsData = $roads->map(fn($r) => [
                    'id' => $r->id, 'name' => $r->name, 'code' => $r->code, 'condition' => $r->condition, 'priority_score' => $r->priority_score
                ]);
            } else {
                $roadsData = DB::table('road_assets')
                    ->orderByDesc('score')
                    ->limit(10)
                    ->get()
                    ->map(fn($r) => [
                        'id' => $r->id, 
                        'name' => $r->road_name, 
                        'code' => $r->road_code, 
                        'condition' => strtolower($r->condition_status), 
                        'priority_score' => $r->score
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
                'village_stats'   => $villageStats
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
}

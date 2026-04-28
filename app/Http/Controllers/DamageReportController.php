<?php

namespace App\Http\Controllers;

use App\Models\DamageReport;
use App\Models\DamagePhoto;
use App\Jobs\ProcessImageAI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DamageReportController extends Controller
{
    public function index()
    {
        try {
            // 1. Get Manual Damage Reports (the ones with photos, AI, etc.)
            $manualReports = DamageReport::with(['photos.aiAnalysis', 'roadAsset'])
                ->latest()
                ->get()
                ->map(function($r) {
                    $r->source = 'manual';
                    return $r;
                });

            // 2. Get Real-time Data from Pulse (roads table)
            // Use try-catch specifically for external table to prevent global crash
            $pulseReports = collect();
            try {
                $pulseReports = \Illuminate\Support\Facades\DB::table('roads')
                    ->select('id', 'name as title', 'condition', 'severity', \Illuminate\Support\Facades\DB::raw('ST_AsText(geom) as wkt'))
                    ->where('condition', '!=', 'baik')
                    ->whereNotNull('geom')
                    ->limit(3000) // Safety limit
                    ->get()
                    ->map(function($road) {
                        $wkt = $road->wkt;
                        $lat = null; $lon = null;
                        
                        if (preg_match('/POINT\(([^ ]+) ([^\)]+)\)/', $wkt, $matches)) {
                            $lon = (float)$matches[1]; $lat = (float)$matches[2];
                        } elseif (preg_match('/LINESTRING\(([^ ]+) ([^,]+)/', $wkt, $matches)) {
                            $lon = (float)$matches[1]; $lat = (float)$matches[2];
                        }

                        return [
                            'id' => 'pulse-' . $road->id,
                            'title' => $road->title ?? 'Unnamed Road',
                            'description' => 'Data Otomatis SISMAP PULSE (Kondisi: ' . $road->condition . ')',
                            'severity' => str_contains(strtolower($road->condition), 'berat') ? 'berat' : 'sedang',
                            'latitude' => $lat,
                            'longitude' => $lon,
                            'status' => 'live',
                            'source' => 'pulse',
                            'photos' => []
                        ];
                    });
            } catch (\Exception $dbEx) {
                \Illuminate\Support\Facades\Log::warning('Sismap Pulse Data Error: ' . $dbEx->getMessage());
                // Continue with manual reports only if pulse fails
            }

            // 3. Merge and Return
            return $manualReports->merge($pulseReports);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DamageReport API Error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Gagal memuat beberapa data peta. Silakan coba lagi.',
                'details' => $e->getMessage()
            ], 500); // We still return 500 but with info, or we could return empty array to prevent white screen
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'road_asset_id' => 'required|exists:road_assets,id',
            'title' => 'required|string|max:255',
            'severity' => 'required|in:ringan,sedang,berat',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'photos' => 'nullable|array',
            'photos.*' => 'image|max:10240', // Max 10MB per photo
        ]);

        $report = DamageReport::create([
            'road_asset_id' => $request->road_asset_id,
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'severity' => $request->severity,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => 'open'
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                // Store in public/damage
                $path = $file->store('damage', 'public');

                $photo = DamagePhoto::create([
                    'damage_report_id' => $report->id,
                    'file_path' => $path,
                    'taken_at' => now(), // We could extract EXIF if needed
                    'uploaded_by' => auth()->id()
                ]);

                // Dispatch Job to process with YOLOv8 (AI Server)
                ProcessImageAI::dispatch($photo);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil disimpan dan sedang diproses oleh AI.',
            'data' => $report->load('photos')
        ], 201);
    }

    public function show($id)
    {
        $report = DamageReport::with(['photos.aiAnalysis', 'roadAsset'])->find($id);
        if (!$report) return response()->json(['error' => 'Report not found'], 404);
        return response()->json($report);
    }
}

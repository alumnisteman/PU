<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RoadAsset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncRoadAssets extends Command
{
    protected $signature = 'sismap:sync-roads';
    protected $description = 'Sinkronisasi otomatis jalan dari SISMAP PULSE ke Admin Portal';

    public function handle()
    {
        $this->info('Memulai sinkronisasi otomatis...');
        
        try {
            $roads = DB::table('roads')->whereNotNull('geom')->get();
            $newCount = 0;

            foreach ($roads as $road) {
                $code = $road->code ?? ('RD-' . str_pad($road->id, 5, '0', STR_PAD_LEFT));
                
                // Gunakan updateOrCreate untuk menghindari duplikasi dan mengupdate data lama
                $asset = RoadAsset::updateOrCreate(
                    ['road_code' => $code],
                    [
                        'road_name' => $road->name ?? 'Jalan Tanpa Nama (' . $road->id . ')',
                        'length_km' => $road->length_km ?? 0,
                        'width_m' => $road->width_m ?? 0,
                        'condition_status' => $road->condition ?? 'baik',
                        'description' => $road->description ?? 'Auto-sync dari SISMAP PULSE',
                        // Koordinat hanya diisi jika aset baru dibuat
                    ]
                );

                if ($asset->wasRecentlyCreated) {
                    // Ekstrak koordinat jika ini adalah data baru
                    $wkt = DB::table('roads')->where('id', $road->id)->select(DB::raw('ST_AsText(geom) as wkt'))->first()->wkt;
                    if (preg_match('/POINT\(([^ ]+) ([^\)]+)\)/', $wkt, $matches)) {
                        $asset->longitude = (float)$matches[1];
                        $asset->latitude = (float)$matches[2];
                    } elseif (preg_match('/LINESTRING\(([^ ]+) ([^,]+)/', $wkt, $matches)) {
                        $asset->longitude = (float)$matches[1];
                        $asset->latitude = (float)$matches[2];
                    }
                    $asset->save();
                    $newCount++;
                }
            }

            $this->info("Berhasil! $newCount jalan baru terdaftar, sisa data diperbarui.");
            Log::info("Sismap Sync: $newCount new roads added.");
            
        } catch (\Exception $e) {
            $this->error('Error saat sinkronisasi: ' . $e->getMessage());
            Log::error('Sismap Sync Error: ' . $e->getMessage());
        }
    }
}

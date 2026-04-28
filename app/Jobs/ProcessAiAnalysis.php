<?php

namespace App\Jobs;

use App\Models\DamagePhoto;
use App\Models\AiAnalysis;
use App\Models\RoadAsset;
use App\Models\DamageReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessAiAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $photo;

    public function __construct(DamagePhoto $photo)
    {
        $this->photo = $photo;
    }

    public function handle(): void
    {
        // Simulasi Delay AI (2-5 detik)
        sleep(rand(2, 5));

        $types = ['pothole', 'crack', 'alligator_cracking', 'rutting'];
        $type = $types[array_rand($types)];
        $score = rand(60, 98) / 100;
        $confidence = rand(85, 99);

        DB::transaction(function() use ($type, $score, $confidence) {
            AiAnalysis::create([
                'damage_photo_id' => $this->photo->id,
                'damage_type' => $type,
                'severity_score' => $score,
                'confidence' => $confidence,
                'bounding_box' => [
                    'x' => rand(100, 500), 'y' => rand(100, 500),
                    'w' => rand(50, 200), 'h' => rand(50, 200)
                ],
                'processed_at' => now()
            ]);

            // Logic Guard: Auto-update Road Status if severity is high
            if ($score > 0.8) {
                $report = $this->photo->report;
                $asset = $report->roadAsset;
                
                // Jika banyak laporan berat, paksa status jalan jadi Rusak Berat
                $heavyReportsCount = DamageReport::where('road_asset_id', $asset->id)
                    ->where('severity', 'berat')
                    ->count();

                if ($heavyReportsCount >= 3 || $score > 0.9) {
                    $asset->update(['condition_status' => 'rusak_berat']);
                }
            }
        });
    }
}

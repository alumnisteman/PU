<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoadAsset;
use App\Models\DamageReport;
use App\Models\DamagePhoto;
use App\Models\AiAnalysis;
use Illuminate\Support\Facades\DB;

class DamageReportSeeder extends Seeder
{
    public function run(): void
    {
        $roads = RoadAsset::all();
        if ($roads->isEmpty()) return;

        $types = ['pothole', 'crack', 'alligator_cracking', 'rutting'];

        foreach ($roads->random(15) as $road) {
            $severity = ['ringan', 'sedang', 'berat'][rand(0, 2)];
            
            $report = DamageReport::create([
                'road_asset_id' => $road->id,
                'title' => 'Temuan Kerusakan ' . $road->road_name,
                'description' => 'Terdeteksi kerusakan permukaan jalan hasil survey lapangan.',
                'severity' => $severity,
                'status' => 'open',
                'latitude' => $road->latitude + (rand(-100, 100) / 100000),
                'longitude' => $road->longitude + (rand(-100, 100) / 100000),
            ]);

            $photo = DamagePhoto::create([
                'damage_report_id' => $report->id,
                'file_path' => 'damage_reports/seed_example.jpg',
                'taken_at' => now(),
            ]);

            AiAnalysis::create([
                'damage_photo_id' => $photo->id,
                'damage_type' => $types[array_rand($types)],
                'severity_score' => rand(60, 95) / 100,
                'confidence' => rand(88, 99),
                'bounding_box' => ['x' => 100, 'y' => 100, 'w' => 50, 'h' => 50],
                'processed_at' => now(),
            ]);
            
            // Trigger auto-update if severity is berat
            if ($severity === 'berat') {
                $road->update(['condition_status' => 'rusak_berat']);
            }
        }
    }
}

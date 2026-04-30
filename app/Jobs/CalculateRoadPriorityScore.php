<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class CalculateRoadPriorityScore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $roadId;

    /**
     * Create a new job instance.
     */
    public function __construct($roadId)
    {
        $this->roadId = $roadId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $road = DB::table('road_assets')->where('id', $this->roadId)->first();
        if (!$road) {
            $road = DB::table('roads')->where('id', $this->roadId)->first();
        }
        if (!$road) return;

        // Base score parsing logic (mocked data values for AI input)
        $kondisi_map = [
            'rusak_berat' => 90,
            'rusak' => 80,
            'rusak_ringan' => 60,
            'sedang' => 40,
            'baik' => 10
        ];
        $kondisiValue = $kondisi_map[strtolower($road->condition_status ?? $road->condition ?? 'baik')] ?? 10;
        
        $length = $road->length_km ?? 1.0;
        
        try {
            // Call FastAPI AI service
            $res = Http::timeout(10)->post(env('AI_URL', 'http://ai:8000') . '/predict', [
                "kondisi" => $kondisiValue,
                "traffic" => 75, // Placeholder
                "rainfall" => 60, // Placeholder
                "age" => 5, // Placeholder
                "reports" => 2, // Placeholder
                "length" => $length
            ]);

            if ($res->successful()) {
                $score = $res->json()['risk_score'] ?? 0;
                
                // Update road assets Priority
                DB::table('road_assets')
                    ->where('id', $this->roadId)
                    ->update(['priority_score' => $score]);
                
                // Keep 'roads' table synced if needed
                DB::table('roads')
                    ->where('id', $this->roadId)
                    ->update(['condition_score' => $score]);
                
                // --- AUTO DISPATCH NOTIFICATION (AI TRIGGER) ---
                if ($score > 85) {
                    try {
                        $factory = (new \Kreait\Firebase\Factory)->withServiceAccount(base_path('firebase.json'));
                        $messaging = $factory->createMessaging();
                        
                        $roadName = $road->road_name ?? $road->name ?? 'Jalan Tidak Diketahui';
                        $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('topic', 'workers')
                            ->withNotification([
                                'title' => 'Tugas Baru: Prioritas Kritis! 🚨',
                                'body'  => "Segera tangani jalan $roadName (Skor: $score). Kerusakan parah."
                            ]);
                        
                        $messaging->send($message);
                        \Illuminate\Support\Facades\Log::info("Auto Dispatch Notification sent for road: $roadName");
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to send Firebase Notification: " . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            // Fallback or retry logic
            \Illuminate\Support\Facades\Log::error("CalculateRoadPriorityScore AI Error: " . $e->getMessage());
        }
    }
}

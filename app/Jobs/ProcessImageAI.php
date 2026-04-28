<?php

namespace App\Jobs;

use App\Models\DamagePhoto;
use App\Models\AiAnalysis;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessImageAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Retry 3 times if AI server is down
    public $backoff = 60; // Wait 60 seconds before retrying

    public function __construct(public DamagePhoto $photo)
    {}

    public function handle()
    {
        try {
            // Path to the image file
            $imagePath = storage_path('app/public/' . $this->photo->file_path);

            if (!file_exists($imagePath)) {
                Log::error("AI Processing failed: File not found at {$imagePath}");
                return;
            }

            // Send to Python AI Server (FastAPI + YOLOv8)
            // Replace 'http://ai-service' with the actual local IP or domain of your AI server
            $response = Http::attach(
                'file', file_get_contents($imagePath), basename($imagePath)
            )->post('http://127.0.0.1:8000/detect');

            if ($response->successful()) {
                $data = $response->json();

                AiAnalysis::create([
                    'damage_photo_id' => $this->photo->id,
                    'damage_type' => $data['type'] ?? 'unknown',
                    'severity_score' => $data['severity'] ?? 0,
                    'confidence' => $data['confidence'] ?? 0,
                    'bounding_box' => $data['boxes'] ?? [],
                    'processed_at' => now()
                ]);

                Log::info("AI Processing successful for photo ID: {$this->photo->id}");
            } else {
                Log::warning("AI Server returned error: " . $response->body());
                throw new \Exception("AI Server error");
            }

        } catch (\Exception $e) {
            Log::error("AI Processing Exception: " . $e->getMessage());
            throw $e; // Re-throw to trigger retry
        }
    }
}

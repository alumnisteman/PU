<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Road;

class ImportRoadsOSM extends Command
{
    protected $signature = 'osm:import-roads';
    protected $description = 'Import roads from OpenStreetMap (Overpass API) for Ternate';

    public function handle()
    {
        $this->info("Fetching roads from OpenStreetMap (Overpass API)...");

        // Query for Ternate Bounding Box (approx)
        $query = '
        [out:json][timeout:60];
        (
          way["highway"~"primary|secondary|tertiary|residential"](0.75,127.35,0.85,127.45);
        );
        out geom;
        ';

        $response = Http::timeout(120)
            ->withHeaders(['User-Agent' => 'SISMAP-Ternate-Import/1.0'])
            ->asForm()
            ->post(
                'https://overpass-api.de/api/interpreter',
                ['data' => $query]
            );

        if (!$response->successful()) {
            $this->error("Failed to fetch from OSM Overpass API. Status: " . $response->status());
            return;
        }

        $data = $response->json();
        $elements = $data['elements'] ?? [];
        $count = 0;
        $updated = 0;

        foreach ($elements as $el) {
            if (!isset($el['geometry'])) continue;

            $coords = [];
            foreach ($el['geometry'] as $point) {
                $coords[] = [(float)$point['lon'], (float)$point['lat']];
            }

            if (count($coords) < 2) continue;

            $osmId = $el['id'];
            $name = $el['tags']['name'] ?? 'Tanpa Nama';
            $highway = $el['tags']['highway'] ?? 'unclassified';
            
            // Calculate center for Lat/Lng
            $centerLat = $el['geometry'][0]['lat'];
            $centerLng = $el['geometry'][0]['lon'];

            // Prepare WKT for geom column
            // MySQL expects LINESTRING(Lng Lat, Lng Lat, ...)
            $wktPoints = collect($el['geometry'])->map(fn($p) => "{$p['lon']} {$p['lat']}")->implode(', ');
            $wkt = "LINESTRING($wktPoints)";

            $exists = DB::table('roads')->where('osm_id', $osmId)->exists();

            DB::table('roads')->updateOrInsert(
                ['osm_id' => $osmId],
                [
                    'name' => $name,
                    'condition' => 'baik',
                    'lat' => $centerLat,
                    'lng' => $centerLng,
                    'geometry' => json_encode([
                        'type' => 'LineString',
                        'coordinates' => $coords
                    ]),
                    'geom' => DB::raw("ST_SRID(ST_GeomFromText('$wkt'), 4326)"),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            if ($exists) $updated++; else $count++;
        }

        $this->info("Success! Imported {$count} new roads, updated {$updated} existing roads.");
    }
}

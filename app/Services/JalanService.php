<?php

namespace App\Services;

use App\Models\RoadAsset;
use App\Models\Region;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class JalanService
{
    public function getAllWithFilters($filters = [], $perPage = 9)
    {
        $query = RoadAsset::query()->with('region');

        if (!empty($filters['province']) || !empty($filters['city']) || !empty($filters['district'])) {
            $query->whereHas('region', function ($q) use ($filters) {
                if (!empty($filters['province'])) $q->where('province', $filters['province']);
                if (!empty($filters['city']))     $q->where('city',     $filters['city']);
                if (!empty($filters['district'])) $q->where('district', $filters['district']);
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Fetch all road assets with coords for map rendering.
     */
    public function getMapData($filters = [])
    {
        $query = RoadAsset::query()->with('region')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if (!empty($filters['province']) || !empty($filters['city']) || !empty($filters['district'])) {
            $query->whereHas('region', function ($q) use ($filters) {
                if (!empty($filters['province'])) $q->where('province', $filters['province']);
                if (!empty($filters['city']))     $q->where('city',     $filters['city']);
                if (!empty($filters['district'])) $q->where('district', $filters['district']);
            });
        }

        return $query->get(['id', 'road_name', 'road_code', 'latitude', 'longitude',
                             'length_km', 'width_m', 'condition_status', 'region_id']);
    }

    public function getById($id)
    {
        return RoadAsset::with('region')->findOrFail($id);
    }

    public function create(array $data, $image = null)
    {
        if (!empty($data['road_name'])) {
            $data['road_name'] = trim($data['road_name']);
        }
        
        if ($image) {
            $filename = md5(time() . $image->getClientOriginalName()) . '.jpg';
            $image->move(public_path('uploads/others/pu/' . date('Y')), $filename);
            $data['photo_url'] = $filename;
        }

        return RoadAsset::create($data);
    }

    public function update($id, array $data, $image = null)
    {
        $asset = RoadAsset::findOrFail($id);

        if (!empty($data['road_name'])) {
            $data['road_name'] = trim($data['road_name']);
        }

        if ($image) {
            $filename = md5(time() . $image->getClientOriginalName()) . '.jpg';
            $image->move(public_path('uploads/others/pu/' . date('Y')), $filename);
            $data['photo_url'] = $filename;
        }

        $asset->update($data);

        // Flush cache dan broadcast ke dashboard via Node.js realtime server
        Cache::flush();
        $condition = strtolower($data['condition_status'] ?? $asset->condition_status);
        try {
            Http::timeout(2)->post('http://127.0.0.1:3000/broadcast', [
                'event' => 'data-refresh',
                'data'  => [
                    'id'        => $id,
                    'asset_id'  => $id,
                    'name'      => $asset->road_name,
                    'condition' => $condition,
                    'lat'       => $asset->latitude,
                    'lng'       => $asset->longitude,
                    'message'   => 'Kondisi jalan diperbarui dari admin panel',
                ],
            ]);
        } catch (\Exception $e) {
            // Silently fail - realtime server mungkin belum jalan
        }

        return $asset;
    }

    public function delete($id)
    {
        $asset = RoadAsset::findOrFail($id);
        $asset->delete();
        return true;
    }
}

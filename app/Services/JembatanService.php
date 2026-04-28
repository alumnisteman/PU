<?php

namespace App\Services;

use App\Models\Jembatan;
use Illuminate\Support\Facades\Cache;

class JembatanService
{
    public function getAll($filters = [], $perPage = 9)
    {
        $cacheKey = 'jembatan_list_' . md5(serialize($filters) . $perPage . request('page', 1));

        return Cache::remember($cacheKey, 60, function () use ($filters, $perPage) {
            $query = Jembatan::query();

            if (!empty($filters['propinsi'])) {
                $query->where('jembatan_propinsi_id', $filters['propinsi']);
            }

            return $query->paginate($perPage);
        });
    }

    public function getById($id)
    {
        return Cache::remember("jembatan_show_{$id}", 60, function () use ($id) {
            return Jembatan::with('details')->findOrFail($id);
        });
    }

    public function store(array $data)
    {
        // Safety: Read-only check can be added here or in controller
        return Jembatan::create($data);
    }
}

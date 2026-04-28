<?php

namespace App\Services;

use App\Models\Propinsi;
use App\Models\Kota;
use App\Models\Kecamatan;
use Illuminate\Support\Facades\Cache;

class WilayahService
{
    public function getProvinces()
    {
        return Cache::remember('provinces_all', 3600, function () {
            return Propinsi::all();
        });
    }

    public function getCitiesByProvince($provinceId)
    {
        return Cache::remember("cities_province_{$provinceId}", 3600, function () use ($provinceId) {
            return Kota::where('kota_propinsi_id', $provinceId)->get();
        });
    }

    public function getDistrictsByCity($cityId)
    {
        return Cache::remember("districts_city_{$cityId}", 3600, function () use ($cityId) {
            return Kecamatan::where('kecamatan_kota_id', $cityId)->get();
        });
    }

    /**
     * Example of complex manual join as per blueprint
     */
    public function getDetailedDistricts()
    {
        return Cache::remember('districts_detailed', 600, function () {
            return \DB::table('wil_kecamatan_tbl as k')
                ->join('wil_kota_tbl as kota', 'k.kecamatan_kota_id', '=', 'kota.kota_id')
                ->select('k.*', 'kota.kota_nama')
                ->limit(100)
                ->get();
        });
    }
}

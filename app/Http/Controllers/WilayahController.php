<?php

namespace App\Http\Controllers;

use App\Services\WilayahService;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    protected $wilayahService;

    public function __construct(WilayahService $wilayahService)
    {
        $this->wilayahService = $wilayahService;
    }

    public function provinces()
    {
        return response()->json($this->wilayahService->getProvinces());
    }

    public function cities($provinceId)
    {
        return response()->json($this->wilayahService->getCitiesByProvince($provinceId));
    }

    public function districts($cityId)
    {
        return response()->json($this->wilayahService->getDistrictsByCity($cityId));
    }

    public function store(Request $req)
    {
        // Data protection: read-only for now as per blueprint
        abort(403, 'Write disabled sementara (Legacy Safe Mode)');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Region;
use App\Services\JalanService;

class JalanController extends Controller
{
    protected $jalanService;

    public function __construct(JalanService $jalanService)
    {
        $this->jalanService = $jalanService;
    }

    /**
     * Map propinsi ID (legacy CodeIgniter param) to province name.
     */
    private function resolveProvince(Request $request): string
    {
        // Legacy param: ?propinsi=32 → Maluku Utara
        $propinsiMap = [
            '32' => 'Maluku Utara',
            '31' => 'DKI Jakarta',
            // extend as needed
        ];

        if ($request->has('propinsi') && isset($propinsiMap[$request->get('propinsi')])) {
            return $propinsiMap[$request->get('propinsi')];
        }

        return $request->get('province', 'Maluku Utara');
    }

    public function index(Request $request)
    {
        $province = $this->resolveProvince($request);
        $city     = $request->get('city',     'Ternate');
        $district = $request->get('district', '');

        $filters = [
            'province' => $province,
            'city'     => $city,
            'district' => $district,
        ];

        $jalans   = $this->jalanService->getAllWithFilters($filters);
        $mapData  = $this->jalanService->getMapData($filters);

        // Dropdown data
        $provinces = Region::distinct()->orderBy('province')->pluck('province');

        $cities = Region::where('province', $province)
            ->distinct()->orderBy('city')->pluck('city');

        $districts = Region::where('province', $province)
            ->where('city', $city)
            ->distinct()->orderBy('district')->pluck('district');

        // Stats for hero
        $totalJalan  = $jalans->total();
        $totalPanjang = \App\Models\RoadAsset::whereHas('region', function ($q) use ($filters) {
            if ($filters['province']) $q->where('province', $filters['province']);
            if ($filters['city'])     $q->where('city',     $filters['city']);
            if ($filters['district']) $q->where('district', $filters['district']);
        })->sum('length_km');

        return view('jalan.index', compact(
            'jalans', 'mapData',
            'provinces', 'cities', 'districts',
            'province', 'city', 'district',
            'totalJalan', 'totalPanjang'
        ));
    }

    public function show($id)
    {
        $jalan = $this->jalanService->getById($id);
        $roadMaterials = \App\Models\RoadMaterial::where('road_id', $id)->with('material')->get();
        $totalCost = 0;
        foreach ($roadMaterials as $rm) {
            $totalCost += $rm->volume * $rm->material->price;
        }

        return view('jalan.show', compact('jalan', 'roadMaterials', 'totalCost'));
    }

    public function create()
    {
        $regions = Region::all();
        return view('jalan.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'region_id'        => 'required|integer',
            'road_name'        => 'required|string|max:200',
            'road_code'        => 'nullable|string|max:50',
            'length_km'        => 'nullable|numeric',
            'width_m'          => 'nullable|numeric',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
            'elevation'        => 'nullable|numeric',
            'description'      => 'nullable|string',
            'condition_status' => 'nullable|in:baik,sedang,rusak_ringan,rusak_berat',
        ]);

        $this->jalanService->create($data, $request->file('photo'));

        return redirect()->route('jalan.index')->with('success', 'Aset jalan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $jalan   = $this->jalanService->getById($id);
        $regions = \App\Models\Region::all();
        $materials = \App\Models\Material::all();
        $roadMaterials = \App\Models\RoadMaterial::where('road_id', $id)->with('material')->get();
        
        $totalCost = 0;
        foreach ($roadMaterials as $rm) {
            $totalCost += $rm->volume * $rm->material->price;
        }

        return view('jalan.edit', compact('jalan', 'regions', 'materials', 'roadMaterials', 'totalCost'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'region_id'        => 'required|integer',
            'road_name'        => 'required|string|max:200',
            'road_code'        => 'nullable|string|max:50',
            'length_km'        => 'nullable|numeric',
            'width_m'          => 'nullable|numeric',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
            'elevation'        => 'nullable|numeric',
            'description'      => 'nullable|string',
            'condition_status' => 'nullable|in:baik,sedang,rusak_ringan,rusak_berat',
        ]);

        $this->jalanService->update($id, $data, $request->file('photo'));

        return redirect()->route('jalan.show', $id)->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->jalanService->delete($id);
        return redirect()->route('jalan.index')->with('success', 'Data berhasil dihapus.');
    }
}

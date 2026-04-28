<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Material;
use App\Models\RoadMaterial;

class MaterialController extends Controller
{
    public function updatePrice(Request $request)
    {
        $request->validate([
            'id'    => 'required|exists:materials,id',
            'price' => 'required|numeric|min:0'
        ]);

        $material = Material::findOrFail($request->id);
        $material->update(['price' => $request->price]);

        return back()->with('success', 'Harga material berhasil diperbarui.');
    }

    public function addRoadMaterial(Request $request)
    {
        $request->validate([
            'road_id'     => 'required|exists:road_assets,id',
            'material_id' => 'required|exists:materials,id',
            'volume'      => 'required|numeric|min:0'
        ]);

        RoadMaterial::updateOrCreate(
            ['road_id' => $request->road_id, 'material_id' => $request->material_id],
            ['volume' => $request->volume]
        );

        return back()->with('success', 'Kebutuhan material berhasil ditambahkan.');
    }

    public function removeRoadMaterial($id)
    {
        $rm = RoadMaterial::findOrFail($id);
        $rm->delete();
        return back()->with('success', 'Material dihapus dari estimasi.');
    }
}

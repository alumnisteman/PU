<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Propinsi;
use App\Models\Kota;
use App\Models\Jembatan;
use App\Services\JembatanService;

class JembatanController extends Controller
{
    protected $jembatanService;

    public function __construct(JembatanService $jembatanService)
    {
        $this->jembatanService = $jembatanService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['propinsi']);
        $jembatans = $this->jembatanService->getAll($filters);
        $propinsis = Propinsi::all();

        return view('jembatan.index', compact('jembatans', 'propinsis'));
    }

    public function show($id)
    {
        $jembatan = $this->jembatanService->getById($id);
        
        return view('jembatan.show', compact('jembatan'));
    }

    public function create()
    {
        $propinsis = Propinsi::all();
        $kotas = Kota::all(); 

        return view('jembatan.create', compact('propinsis', 'kotas'));
    }

    public function store(Request $request)
    {
        // For now, basic validation. In production, use JembatanRequest
        $data = $request->validate([
            'jembatan_nama' => 'required|string|max:255',
            'jembatan_propinsi_id' => 'required|integer',
            'jembatan_kota_id' => 'required|integer',
            'jembatan_panjang' => 'required|numeric',
            'jembatan_lebar' => 'required|numeric',
        ]);

        $this->jembatanService->store($data);

        return redirect()->route('jembatan.index')->with('success', 'Data jembatan baru berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $jembatan = $this->jembatanService->getById($id);
        $propinsis = Propinsi::all();
        $kotas = Kota::all();

        return view('jembatan.edit', compact('jembatan', 'propinsis', 'kotas'));
    }

    public function update(Request $request, $id)
    {
        $jembatan = Jembatan::findOrFail($id);
        $data = $request->validate([
            'jembatan_nama' => 'required|string|max:255',
            'jembatan_propinsi_id' => 'required|integer',
            'jembatan_kota_id' => 'required|integer',
            'jembatan_panjang' => 'required|numeric',
            'jembatan_lebar' => 'required|numeric',
        ]);

        $jembatan->update($data);

        return redirect()->route('jembatan.show', $id)->with('success', 'Data jembatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $jembatan = Jembatan::findOrFail($id);
        $jembatan->delete();

        return redirect()->route('jembatan.index')->with('success', 'Data jembatan berhasil dihapus.');
    }
}

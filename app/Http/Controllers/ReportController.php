<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Road;
use App\Models\Jalan;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function printJalan($id)
    {
        $jalan = Jalan::with(['details', 'kondisis', 'perkerasans', 'penanganans'])->findOrFail($id);
        $pdf = Pdf::loadView('reports.jalan_single', compact('jalan'));
        return $pdf->download("Laporan_Jalan_{$jalan->jalan_kode}.pdf");
    }

    public function printRoads()
    {
        $roads = Road::with('region')->get()->sortByDesc(function($road) {
            return $road->priority_score;
        });

        $pdf = Pdf::loadView('reports.roads_list', compact('roads'));
        return $pdf->download("Laporan_Prioritas_Jalan_" . date('Y-m-d') . ".pdf");
    }
}
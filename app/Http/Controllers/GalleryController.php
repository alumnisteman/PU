<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JalanDetail;
use App\Models\JembatanDetail;

class GalleryController extends Controller
{
    public function index()
    {
        $jalanPhotos = JalanDetail::with('jalan')->whereNotNull('detail_foto_alias')->where('detail_foto_alias', '!=', '')->select('detail_id', 'detail_jalan_id', 'detail_foto_alias', 'detail_tahun')->get();
        $jembatanPhotos = collect(); // Returning empty collection for now as the column doesn't exist

        return view('gallery.index', compact('jalanPhotos', 'jembatanPhotos'));
    }
}

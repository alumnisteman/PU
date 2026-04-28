<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function store(Request $req)
    {
        $req->validate([
            'file' => 'required|file|mimes:jpg,png,pdf|max:2048'
        ]);

        // Secure upload path based on current year for organization
        $path = $req->file('file')->store('uploads/' . date('Y'), 'public');

        return response()->json([
            'path' => $path,
            'url' => asset('storage/' . $path)
        ]);
    }
}

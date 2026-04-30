<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function store(Request $req)
    {
        $uploadField = $req->hasFile('photo') ? 'photo' : 'file';
        
        $req->validate([
            $uploadField => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240' // Increased max size for mobile photos
        ]);

        // Secure upload path based on current year for organization
        $path = $req->file($uploadField)->store('uploads/' . date('Y'), 'public');

        return response()->json([
            'path' => $path,
            'url' => asset('storage/' . $path)
        ]);
    }
}

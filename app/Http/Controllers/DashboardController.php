<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jalan;
use App\Models\Jembatan;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        return view('admin.dashboard');
    }
}

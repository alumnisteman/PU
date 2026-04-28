<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function register(Request $req)
    {
        $req->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:core_users,user_email',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'user_fullname' => $req->name,
            'user_name' => explode('@', $req->email)[0], // fallback username
            'user_email' => $req->email,
            'user_password' => Hash::make($req->password),
            'user_level_id' => 2, // Default level for new users
        ]);

        return response()->json($user);
    }

    public function login(Request $req)
    {
        $credentials = $req->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['user_email' => $credentials['email'], 'password' => $credentials['password']])) {
            $req->session()->regenerate();
            return redirect()->intended('/admin');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $req)
    {
        // For API (Sanctum)
        if ($req->user()) {
            $req->user()->tokens()->delete();
        }
        
        // For Web session
        Auth::logout();
        $req->session()->invalidate();
        $req->session()->regenerateToken();

        return redirect('/');
    }
}

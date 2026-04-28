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
        return view('welcome'); // Or a dedicated login view if created
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
        $req->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('user_email', $req->email)->first();

        if (!$user || !Hash::check($req->password, $user->user_password)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function logout(Request $req)
    {
        // For API (Sanctum)
        if ($req->user()) {
            $req->user()->tokens()->delete();
        }
        
        // For Web session
        Auth::logout();

        return response()->json(['message' => 'Logged out']);
    }
}

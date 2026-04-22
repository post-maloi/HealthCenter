<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Don't forget to import the User model
use Illuminate\Support\Facades\Hash; // Import for password encryption

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    // ADD THIS: Show the registration form
    public function showRegister()
    {
        return view('register');
    }

    // ADD THIS: Handle the registration logic
 public function register(Request $request)
{
    // 1. Update Validation
    $request->validate([
        'first_name' => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'role' => 'nullable|in:bhw,doctor,nurse',
    ]);

    // 2. Create User
    $user = User::create([
        'first_name' => $request->first_name,
        'middle_name' => $request->middle_name,
        'last_name' => $request->last_name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->input('role', 'bhw'),
    ]);

    // 3. Redirect to Login (Instead of auto-logging in)
    return redirect()->route('login')->with('success', 'Registration successful! Please sign in.');
}

   // App/Http/Controllers/AuthController.php

public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        // Redirect to /dashboard, NOT just /
        return redirect()->intended('/dashboard');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
}

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login'); // Redirect to login after logout
    }
}
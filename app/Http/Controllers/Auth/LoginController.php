<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        // 1. Cek Blokir Manual
        if ($user && $user->is_blocked) {
            return back()->withErrors(['email' => 'Afwan, Akun antum sedang ditangguhkan/diblokir oleh sistem demi keamanan. Hubungi Super Admin.'])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->has('remember'))) {
            // Berhasil login
            $user = auth()->user();

            // 2. Beri izin login admin lama & baru
            if (!$user->canAccessAdmin()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Hanya Administrator yang diperbolehkan masuk ke panel ini.'])->onlyInput('email');
            }

            // Reset failed count
            $user->update(['failed_login_count' => 0]); 

            // Log Success
            \DB::table('login_attempts')->insert([
                'email' => $credentials['email'],
                'ip_address' => $request->ip(),
                'is_successful' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $request->session()->regenerate();
            return redirect()->intended('admin/dashboard');
        }

        // Gagal Login
        if ($user) {
            $user->increment('failed_login_count');
            if ($user->failed_login_count >= 5) {
                $user->update(['is_blocked' => true]);
                // Log and Block message
                return back()->withErrors(['email' => 'Afwan, Akun antum otomatis diblokir karena 5x gagal login. Hubungi Admin.'])->onlyInput('email');
            }
        }

        // Log Failure
        \DB::table('login_attempts')->insert([
            'email' => $credentials['email'],
            'ip_address' => $request->ip(),
            'is_successful' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->withErrors([
            'email' => 'Afwan, Email atau password antum salah. Sisa kesempatan login: ' . ($user ? max(0, 5 - $user->failed_login_count) : '5'),
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}

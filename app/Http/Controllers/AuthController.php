<?php

namespace App\Http\Controllers;

use App\Models\LanguageGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        // Validasi email dan password
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Cari pengguna berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Periksa akun dan password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors([
                    'login' => 'Email atau password salah.',
                ])
                ->onlyInput('email', 'kode_kelompok');
        }

        // Admin tidak wajib memiliki kelompok bahasa
        if ($user->role !== 'admin') {
            $request->validate([
                'kode_kelompok' => ['required', 'string'],
            ]);

            $group = LanguageGroup::where(
                'code',
                $request->kode_kelompok
            )->first();

            // Periksa kode kelompok dan kelompok milik pengguna
            if (
                !$group ||
                (int) $user->language_group_id !== (int) $group->id
            ) {
                return back()
                    ->withErrors([
                        'login' => 'Kode kelompok bahasa tidak sesuai dengan akun.',
                    ])
                    ->onlyInput('email', 'kode_kelompok');
            }
        }

        // Login pengguna
        Auth::login($user);
        $request->session()->regenerate();

        // Arahkan sesuai role
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'pengajar') {
            return redirect()->route('dashboard.pengajar');
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

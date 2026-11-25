<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Tampilkan halaman lupa password.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Reset password langsung jika email terdaftar (tanpa kirim email).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.exists' => 'Email tidak terdaftar.',
        ]);

        User::where('email', $request->email)->first()->update([
            'password' => Hash::make($request->password),
            'force_password_reset' => false,
        ]);

        return redirect()->route('login')
            ->with('status', 'Password berhasil diubah. Silakan login dengan password baru.');
    }
}
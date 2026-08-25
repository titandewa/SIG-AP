<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $user = User::where('email', $request->input('email'))->first();

        // Kalau akun ini lagi status reset, jangan lanjut login biasa - munculin modal set password baru
        if ($user && $user->force_password_reset) {
            return back()
                ->withInput($request->only('email'))
                ->with('show_reset_modal', true)
                ->with('reset_email', $user->email);
        }

        $request->authenticate();

        $request->session()->regenerate();

        $userRole = strtolower(Auth::user()->role ?? '');

        switch ($userRole) {
            case 'aspri':
                return redirect()->route('app.aspri.dashboard');
            case 'petinggi':
                return redirect()->route('app.petinggi.dashboard');
            case 'dirut':
                return redirect()->route('app.dirut.dashboard');
            case 'admin':
                return redirect()->route('app.admin.dashboard');
            default:
                return redirect()->route('login');
        }
    }

    /**
     * Set password baru untuk akun yang statusnya lagi di-reset admin.
     */
    public function resetForced(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)
            ->where('force_password_reset', true)
            ->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Permintaan reset tidak valid atau sudah kadaluarsa.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'force_password_reset' => false,
        ]);

        return redirect()->route('login')->with('status', 'Password berhasil diperbarui. Silakan login dengan password baru.');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleManager
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect()->guest(route('login'));
        }

        $userRole = strtolower(Auth::user()->role);
        $targetRole = strtolower($role);

        // Cek apakah role user sesuai dengan yang diizinkan
        if ($userRole !== $targetRole) {
            // Redirect sesuai role milik user jika mencoba akses halaman lain
            switch ($userRole) {
                case 'aspri':
                    return redirect()->route('app.aspri.dashboard');
                case 'petinggi':
                    return redirect()->route('app.petinggi.dashboard');
                case 'dirut':
                    return redirect()->route('app.dirut.dashboard');
                default:
                    abort(403, 'Akses tidak diizinkan.');
            }
        }

        return $next($request);
    }
}
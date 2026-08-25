<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordReset
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()
            && $request->user()->force_password_reset
            && ! $request->routeIs('password.force-update')
            && ! $request->routeIs('logout')) {
            return redirect()->route('password.force-update');
        }

        return $next($request);
    }
}
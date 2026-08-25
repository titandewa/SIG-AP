<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Mendaftarkan alias middleware role custom
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleManager::class,
            'force.password.reset' => \App\Http\Middleware\ForcePasswordReset::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\ForcePasswordReset::class);

        // Percaya header X-Forwarded-* dari proxy (ngrok/Laragon) biar Laravel
        // baca host & scheme asli dari browser, bukan dari proxy lokal.
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
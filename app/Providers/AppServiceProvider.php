<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $request = $this->app['request'];
        $host = $request->getHost();

        // Jika diakses melalui ngrok, gunakan pengaturan proxy/ngrok
        if (str_contains($host, 'ngrok-free.app') || str_contains($host, 'ngrok.io')) {
            $forwardedHost = $request->headers->get('x-forwarded-host');
            $forwardedScheme = $request->headers->get('x-forwarded-proto');

            if ($forwardedHost && $forwardedScheme) {
                $rootUrl = $forwardedScheme . '://' . $forwardedHost;
                config(['app.url' => $rootUrl]);
                URL::forceRootUrl($rootUrl);
                URL::forceScheme($forwardedScheme);
            }
        }
    }
}
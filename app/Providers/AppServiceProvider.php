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

        $host = $request->headers->get('x-forwarded-host') ?? $request->getHost();
        $scheme = $request->headers->get('x-forwarded-proto') ?? $request->getScheme();

        $rootUrl = $scheme . '://' . $host;

        if (!empty($rootUrl)) {
            config(['app.url' => $rootUrl]);
            URL::forceRootUrl($rootUrl);
            URL::forceScheme($scheme);
        }
    }
}

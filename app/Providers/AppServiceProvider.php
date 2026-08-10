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
        $rootUrl = $request->getSchemeAndHttpHost();

        if (!empty($rootUrl)) {
            config(['app.url' => $rootUrl]);
            URL::forceRootUrl($rootUrl);
            URL::forceScheme($request->getScheme());
        }
    }
}

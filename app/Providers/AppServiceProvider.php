<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /*
         * Prevent Laravel from generating HTTP image URLs
         * when the website is running on HTTPS.
         */
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
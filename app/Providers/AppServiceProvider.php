<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        // Public catalogue API: generous, because Nepali ISPs put many customers behind one
        // shared (CGNAT) address, but enough to stop scrapers and request floods.
        // A storefront page view makes about 3–6 calls.
        RateLimiter::for('storefront', fn (Request $request) => Limit::perMinute(300)->by($request->ip()));
    }
}

<?php

namespace App\Providers;

use App\Support\Geolocation;
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
        // Shared so IP lookups reuse one cache; tests swap a fake instance.
        $this->app->singleton(Geolocation::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cap how fast a single IP can cast votes, blunting scripted
        // ballot-stuffing on top of the one-vote-per-browser cookie rule.
        RateLimiter::for('votes', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });
    }
}

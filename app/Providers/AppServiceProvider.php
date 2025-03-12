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
        // Cap how fast a single IP can cast votes, blunting scripted
        // ballot-stuffing on top of the one-vote-per-browser cookie rule.
        RateLimiter::for('votes', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });
    }
}

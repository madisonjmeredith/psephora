<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kill switch
    |--------------------------------------------------------------------------
    |
    | When disabled, App\Support\Geolocation::locate() short-circuits to null
    | and no lookups (or external requests) happen. Polls fall back to their
    | newest-first ordering and simply carry no location. Handy for CI, or if a
    | provider is misbehaving. Mirrors the reference app's `disable_geoip`.
    |
    */

    'enabled' => env('GEOLOCATION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Lookup cache
    |--------------------------------------------------------------------------
    |
    | A resolved IP rarely changes location, so each lookup is cached for this
    | many hours to spare the (rate-limited, network-bound) driver on repeat
    | visits — the poll list resolves the visitor's IP on every page load.
    |
    */

    'cache_ttl_hours' => env('GEOLOCATION_CACHE_TTL_HOURS', 24),

];

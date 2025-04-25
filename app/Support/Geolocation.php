<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Stevebauman\Location\Facades\Location;
use Throwable;

/**
 * Resolves a request IP to an approximate location and measures distances.
 *
 * This is the psephora analog of the reference app's Geoip model: it wraps the
 * swappable-driver location package behind one small surface, guarantees a
 * lookup never breaks a request (bad IP, rate-limit, network blip → null), and
 * honours the `geolocation.enabled` kill switch. Bind as a singleton so lookups
 * share the cache and tests can swap a fake.
 */
class Geolocation
{
    /**
     * Approximate location for an IP, or null when it can't be determined.
     *
     * Public IPs are resolved directly; anything else (empty, private, loopback)
     * defers to the package so its localhost "testing IP" can stand in during
     * development. Results — including misses — are cached so repeat visits skip
     * the (rate-limited, network-bound) driver.
     *
     * @return array{city: ?string, region: ?string, country_code: ?string, latitude: float, longitude: float}|null
     */
    public function locate(?string $ip): ?array
    {
        if (! config('geolocation.enabled', true)) {
            return null;
        }

        $ip = is_string($ip) ? trim($ip) : '';

        $public = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        $lookupIp = $public !== false ? $ip : null;

        $cacheKey = 'geo:'.($lookupIp ?? 'local');
        $ttl = now()->addHours((int) config('geolocation.cache_ttl_hours', 24));

        // Wrap in ['result' => ...] so negative lookups (null) are cached too —
        // Cache::remember alone treats a null return as "not cached".
        return Cache::remember($cacheKey, $ttl, fn (): array => ['result' => $this->resolve($lookupIp)])['result'];
    }

    /**
     * Great-circle distance between two points, in miles.
     *
     * Haversine, ported from the reference app's Geoip::getDistance(). The unit
     * only affects a displayed figure — proximity ordering is unit-independent.
     */
    public function distanceMiles(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusMiles = 3958.8;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadiusMiles * 2 * asin(min(1.0, sqrt($a)));
    }

    /**
     * Perform the actual driver lookup, normalising to our array shape or null.
     *
     * @return array{city: ?string, region: ?string, country_code: ?string, latitude: float, longitude: float}|null
     */
    protected function resolve(?string $ip): ?array
    {
        try {
            $position = Location::get($ip);
        } catch (Throwable) {
            return null;
        }

        if (! $position || $position->latitude === null || $position->longitude === null) {
            return null;
        }

        return [
            'city' => $position->cityName,
            'region' => $position->regionName,
            'country_code' => $position->countryCode,
            'latitude' => (float) $position->latitude,
            'longitude' => (float) $position->longitude,
        ];
    }
}

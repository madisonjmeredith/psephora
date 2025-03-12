<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureVoterToken
{
    /**
     * Cookie name holding the anonymous voter's identity.
     */
    public const COOKIE = 'voter_token';

    /**
     * Ensure every visitor carries a stable, signed voter token.
     *
     * The token identifies an anonymous browser so a single browser can only
     * vote once per poll (enforced by the unique index on the votes table).
     * Laravel's EncryptCookies middleware signs and encrypts the value, so it
     * can't be forged client-side.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie(self::COOKIE);

        if (! is_string($token) || $token === '') {
            $token = (string) Str::uuid();

            // Expose it to the current request so a first-visit vote can use it.
            $request->cookies->set(self::COOKIE, $token);
        }

        // (Re)issue a long-lived cookie so the token survives future visits.
        Cookie::queue(Cookie::forever(self::COOKIE, $token, httpOnly: true, sameSite: 'lax'));

        return $next($request);
    }

    /**
     * Resolve the current request's voter token.
     */
    public static function current(Request $request): string
    {
        return (string) $request->cookie(self::COOKIE);
    }
}

<?php

namespace App\Support;

use Locale;

/**
 * Turns a raw (city, region, country_code) triple into a human display string.
 *
 * The rules, shared by every place we show where a poll came from:
 *   - United States → "City, ST" (the state abbreviation), e.g. "Seattle, WA".
 *   - Anywhere else → just the country name, e.g. "Canada" — the city is
 *     dropped, since a bare foreign city rarely tells the reader much.
 *
 * `region` holds the full state/province name ("Washington"), so US labels look
 * it up in the abbreviation map. Country names come from ICU (ext-intl) keyed by
 * the ISO country code, so we don't hand-maintain a 200-entry country list.
 */
class LocationLabel
{
    /**
     * Full US state/territory name (lower-cased) → USPS abbreviation.
     */
    private const STATE_ABBREVIATIONS = [
        'alabama' => 'AL', 'alaska' => 'AK', 'arizona' => 'AZ', 'arkansas' => 'AR',
        'california' => 'CA', 'colorado' => 'CO', 'connecticut' => 'CT', 'delaware' => 'DE',
        'district of columbia' => 'DC', 'florida' => 'FL', 'georgia' => 'GA', 'hawaii' => 'HI',
        'idaho' => 'ID', 'illinois' => 'IL', 'indiana' => 'IN', 'iowa' => 'IA',
        'kansas' => 'KS', 'kentucky' => 'KY', 'louisiana' => 'LA', 'maine' => 'ME',
        'maryland' => 'MD', 'massachusetts' => 'MA', 'michigan' => 'MI', 'minnesota' => 'MN',
        'mississippi' => 'MS', 'missouri' => 'MO', 'montana' => 'MT', 'nebraska' => 'NE',
        'nevada' => 'NV', 'new hampshire' => 'NH', 'new jersey' => 'NJ', 'new mexico' => 'NM',
        'new york' => 'NY', 'north carolina' => 'NC', 'north dakota' => 'ND', 'ohio' => 'OH',
        'oklahoma' => 'OK', 'oregon' => 'OR', 'pennsylvania' => 'PA', 'rhode island' => 'RI',
        'south carolina' => 'SC', 'south dakota' => 'SD', 'tennessee' => 'TN', 'texas' => 'TX',
        'utah' => 'UT', 'vermont' => 'VT', 'virginia' => 'VA', 'washington' => 'WA',
        'west virginia' => 'WV', 'wisconsin' => 'WI', 'wyoming' => 'WY',
        'american samoa' => 'AS', 'guam' => 'GU', 'northern mariana islands' => 'MP',
        'puerto rico' => 'PR', 'u.s. virgin islands' => 'VI', 'virgin islands' => 'VI',
    ];

    /**
     * Build the display label, or null when there's nothing worth showing.
     */
    public static function make(?string $city, ?string $region, ?string $countryCode): ?string
    {
        $city = self::trimToNull($city);
        $region = self::trimToNull($region);
        $code = strtoupper((string) self::trimToNull($countryCode));

        if ($code === 'US') {
            if ($city === null) {
                return $region;
            }

            $abbreviation = self::stateAbbreviation($region);

            return $abbreviation !== null ? "{$city}, {$abbreviation}" : $city;
        }

        if ($code !== '') {
            // Non-US: show only the country. Fall back to the city if the code
            // can't be resolved to a name, so we still show *something*.
            return self::countryName($code) ?? $city;
        }

        // No country code at all (e.g. coordinate-only test fixtures) — the
        // city is the most specific thing we have.
        return $city;
    }

    /**
     * USPS abbreviation for a state/region name, or null if we don't know it.
     * A value that's already a two-letter code is passed through uppercased.
     */
    private static function stateAbbreviation(?string $region): ?string
    {
        if ($region === null) {
            return null;
        }

        $abbreviation = self::STATE_ABBREVIATIONS[strtolower($region)] ?? null;
        if ($abbreviation !== null) {
            return $abbreviation;
        }

        return preg_match('/^[A-Za-z]{2}$/', $region) === 1 ? strtoupper($region) : null;
    }

    /**
     * English country name for an ISO 3166-1 alpha-2 code, or null if unknown.
     */
    private static function countryName(string $code): ?string
    {
        if (! class_exists(Locale::class)) {
            return null;
        }

        $name = Locale::getDisplayRegion('-'.$code, 'en');

        // ICU echoes the code back for an unknown region and returns
        // "Unknown Region" for the reserved ZZ code — treat both as no match.
        if ($name === '' || $name === 'Unknown Region' || strcasecmp($name, $code) === 0) {
            return null;
        }

        return $name;
    }

    private static function trimToNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}

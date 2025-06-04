<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Return all riders whose location (lat_long JSON) is within $radiusKm kilometers
 * of the given [$customerLat, $customerLng].
 *
 * @param  float  $customerLat   Latitude of the customer (e.g. 24.8607)
 * @param  float  $customerLng   Longitude of the customer (e.g. 67.0011)
 * @param  float  $radiusKm      Radius in kilometers (default 2.5 km for a 5 km diameter)
 * @return \Illuminate\Support\Collection  Collection of User models (role='rider') with an appended `distance` field
 */
function getNearbyRiders(float $customerLat, float $customerLng, float $radiusKm = 2.5)
{
    //
    // 1) Build the Haversine formula as a raw SQL snippet, pulling lat & lng out of JSON:
    //
    //    JSON_UNQUOTE(JSON_EXTRACT(`lat_long`, '$[0]'))   → latitude (string) → unquoted
    //    JSON_UNQUOTE(JSON_EXTRACT(`lat_long`, '$[1]'))   → longitude
    //
    //    We wrap those in RADIANS(…) and apply the standard formula:
    //
    $haversineRaw = "
        (
          6371 * acos(
            cos(radians($customerLat))
            * cos(radians(JSON_UNQUOTE(JSON_EXTRACT(`lat_long`, '$[0]'))))
            * cos(
                radians(JSON_UNQUOTE(JSON_EXTRACT(`lat_long`, '$[1]')))
                - radians($customerLng)
              )
            + sin(radians($customerLat))
            * sin(radians(JSON_UNQUOTE(JSON_EXTRACT(`lat_long`, '$[0]'))))
          )
        )
    ";

    //
    // 2) Use Query Builder to select all riders + that computed distance AS distance.
    //    Then use havingRaw("distance <= ?", [$radiusKm]) to filter by the alias.
    //
    return User::select([
            'users.*',
            DB::raw("$haversineRaw AS distance")
        ])
        ->where('role', 'rider')
        // Filter by the computed distance alias
        ->havingRaw("distance <= ?", [$radiusKm])
        ->orderBy('distance', 'asc')
        ->get();
}

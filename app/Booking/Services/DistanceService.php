<?php

namespace App\Booking\Services;

use App\Booking\Address;
use Illuminate\Support\Facades\Cache;
use Pnlinh\GoogleDistance\Contracts\GoogleDistanceContract;
use Pnlinh\GoogleDistance\Response as GoogleDistanceResponse;

class DistanceService
{
    private $googleDistance;

    public function __construct()
    {
        $this->googleDistance = app()->make(GoogleDistanceContract::class, [
            'apiKey' => config('google-distance.api_key'),
        ]);
    }

    /**
     * Generate the cache key.
     *
     * @param $startLocation
     * @param $destinationLocation
     * @return string
     */
    private function generateDistanceCacheKey($startLocation, $destinationLocation): string
    {
        return 'distance_'.strtoupper(trim($startLocation)).'|^|'.strtoupper(trim($destinationLocation));
    }

    /**
     * Get the distance from Google, then cache it forever.
     *
     * @param string $originPostcode
     * @param string $originAddress
     * @param string $destinationLocation
     * @return GoogleDistanceResponse
     */
    public function getDistance(string $originPostcode, string $originAddress, string $destinationLocation): GoogleDistanceResponse
    {
        $key = $this->generateDistanceCacheKey($originPostcode, $destinationLocation);

        return Cache::rememberForever($key, function () use ($originAddress, $destinationLocation) {
            return $this->googleDistance->calculate($originAddress, $destinationLocation);
        });
    }
}

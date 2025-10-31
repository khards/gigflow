<?php

namespace Tests\Feature\Booking\Availability\Mock;

use Pnlinh\GoogleDistance\Response;

class MockGoogleDistance extends \Pnlinh\GoogleDistance\GoogleDistance
{
    /**
     * Calculate distance from origins to destinations.
     *
     * @param string $origins
     * @param string $destinations
     *
     * @return int
     */
    public function calculate($origins, $destinations): Response
    {
        $distances = [
            'PL48LY' => [150 * 1609, 200 * 60],     // 150 miles. 200 mins
            'CT170BS' => [118 * 1609, 200 * 60],    // 118 miles, 200 mins
            'LO00CA' => [5 * 1609, 10 * 60],        //   5 miles, 10 mins
            'TA64RN' => [10 * 1609, 10 * 60],        //   10 miles, 10 mins
            'TA65RN' => [11 * 1609, 11 * 60],        //   10 miles, 10 mins
            'TA12LP' => [21 * 1609, 17 * 60],        //
        ];

        $destination = str_replace(' ', '', $destinations);
        $destination = strtoupper($destination);

        $response = new Response();
        if (isset($distances[$destination])) {
            list($response->distance_value, $response->duration_value) = $distances[$destination];
        } else {
            $response->distance_value = 11;     // 11 meters
            $response->duration_value = 12;     // 12 seconds
        }
        $response->status = 'OK';

        return $response;
    }
}

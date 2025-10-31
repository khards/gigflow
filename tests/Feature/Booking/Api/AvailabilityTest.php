<?php

namespace Tests\Feature\Booking\Api;

use Tests\Feature\Booking\BookingTestDataGenerator;

class AvailabilityTest extends BookingTestDataGenerator
{
    public const DATE_START_OK = '2020-08-22 19:30:00';

    public const DATE_END_OK = '2020-08-23 01:00:00';

    public const LOCATION_OK = 'TA9 3RS';

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAvailabilityApi()
    {
        $this->createBusinessWithData();
        $user = $this->business->users()->first();

        $uri = route('api.availability.check');

        $postData = [
            'start' => self::DATE_START_OK,
            'end' => self::DATE_END_OK,
            'location' => self::LOCATION_OK,
            'business' => $this->business->id,
        ];

        $this->actingAs($user, 'api');

        $response = $this->post($uri, $postData);
        $response->assertJson([
            'data' => [
                'available' => true,
                'message' => 'Available',
            ],
        ]);

        $response->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Booking\Api;

use App\Booking\Availability\Checker\Checker;
use App\Booking\Contracts\ProductManager;
use App\Booking\Product\Exceptions\ProductPriceNotAvailable;
use App\Booking\Services\ScheduleManager;
use App\Domains\Auth\Models\User;
use Pnlinh\GoogleDistance\Contracts\GoogleDistanceContract;
use Tests\Feature\Booking\Availability\Mock\MockGoogleDistance;
use Tests\Feature\Booking\BookingTestDataGenerator;
use Tests\Feature\Booking\ProductGenerator;

class GetProductsTest extends BookingTestDataGenerator
{
    use ProductGenerator;

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var ScheduleManager
     */
    protected $scheduleManager;

    /**
     * @var Checker
     */
    private $checker;

    /**
     * Setup a product manager instance.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->productManager = app()->make(ProductManager::class);

        $this->business = $this->getBusiness();
        $this->user = User::factory()->create();
        $this->business->users()->attach($this->user->id);
        $this->scheduleManager = $this->app->make(ScheduleManager::class);
        $this->checker = new Checker();
        $this->product = $this->createDefaultServiceProduct($this->business);
        $this->importUserSchedule();
        $this->createAndAttachProductAndBusinessSchedule($this->product, $this->business);
    }

    /**
     * A basic test example.
     *
     * Staff schedule:
     *  DTSTART;VALUE=DATE:         2020-09-09
     *  DTEND;VALUE=DATE:           2020-09-10
     *  RRULE:FREQ=YEARLY
     *
     *  DTSTART;TZID=Europe/London: 2020-10-10 T 06:00:00
     *  DTEND;TZID=Europe/London:   2020-10-17 T 02:00:00
     *  RRULE:FREQ=YEARLY
     *
     *  DTSTART;TZID=Europe/London: 2020-11-11 T 12:00:00
     *  DTEND;TZID=Europe/London:   2020-11-11 T 14:30:00
     *
     * Product schedule:
     *  'start_datetime' => '2021-09-09 00:00:00', 1
     *  'end_datetime' => 2021-09-11 12:00:00', 2
     *
     * @return void
     */
    public function test_get_products_with_dates_location_is_available()
    {
        // Scheduled product price
        $this->product->update(['price_type' => 'scheduled']);

        // With schedule 1 2021-09-09 0:00:00 - 2021-09-10 00:00:00 = £123.27 per booking
        $schedule = $this->scheduleManager->create($this->business, [
            'summary' => 'Scheduled price 1',
            'start_datetime' => '2021-09-09 0:00:00',
            'end_datetime' => '2021-09-10 00:00:00',
        ]);
        $this->product->schedules()->save($schedule, [
            'key' => 'booking',
            'value' => '12327',
        ]);

        // With schedule 1 2021-10-10 00:00:00 - 2021-10-17 12:00:00 = £153.35 per booking
        $schedule2 = $this->scheduleManager->create($this->business, [
            'summary' => 'Scheduled price 2',
            'start_datetime' => '2021-10-10 00:00:00',
            'end_datetime' => '2021-10-17 12:00:00',
        ]);
        $this->product->schedules()->save($schedule2, [
            'key' => 'booking',
            'value' => '15335',
        ]);

        // As API user
        $this->actingAs($this->user, 'api');

        //With the test google distance calculator
        $this->app->bind(GoogleDistanceContract::class, MockGoogleDistance::class);

        $request = [
            'business' => $this->business->id,
            'start' => '2021-09-09 19:00:00',
            'end' =>   '2021-09-10 00:00:00',
            'location' => 'ta93rs',
            'available' => true,
            'state' => 'active',
            'opt_fields' => 'price',
        ];

        $testResponse = [
            'id' => 1,
            'description' => 'Hire of booking system',
            //'extra' => NULL,
//            'force' => false,
            //'image' => '/public/products/1/jx9w4fraOKP1ggHz3PweUyxaRB5oAA5kumnhEKZc',
            'price' => '123.00',//round('123.27'),
            'quantityAvailable' => 1,
            'status' => 'available',
            'name' => 'Elite booking systems',
            'type' => 'service',
            'url' => 'https://elitebookingsystem.com/',
        ];

        /**
         * We are checking here that when we book on 2021-09-09 19:00:00 to 2021-09-10 00:00:00, staff are available and
         * the correct price of 123.27 for the booking is returned.
         */
        $response = $this->get(route('api.products.get', $request), ['accept' => 'application/json']);
        $response->assertStatus(200)->assertJsonFragment($testResponse);

        /**
         * We are checking here that when we book on 2021-10-11 15:00:00 to 2021-10-12 00:00:00, staff are available and
         * the correct price of 153.35 (153.00) rounded is returned.
         */
        $response = $this->get(
            route(
                'api.products.get',
                array_merge($request, ['start' => '2021-10-11 15:00:00', 'end' => '2021-10-12 00:00:00'])
            ),
            ['accept' => 'application/json']
        );
        $response->assertStatus(200)->assertJsonFragment(array_merge($testResponse, ['price' => '153.00']));

        //Should return no products for this date outside of scheduled range.
        $response = $this->get(
            route(
                'api.products.get',
                array_merge($request, ['start' => '2021-12-11 15:00:00', 'end' => '2021-12-12 00:00:00', 'location' => 'ex11SA'])
            ),
            ['accept' => 'application/json']
        );
        $response->assertStatus(200)->assertJsonFragment(['data' => []]);
    }

    /**
     * Test that when no products are available that it doesn't fall over.
     *
     *
     * @return void
     */
    public function test_get_products_none_available()
    {
        // As API user
        $this->actingAs($this->user, 'api');

        //With the test google distance calculator
        $this->app->bind(GoogleDistanceContract::class, MockGoogleDistance::class);

        $request = [
            'business' => $this->business->id,
            'start' => '2026-09-09 19:00:00',
            'end' => '2029-09-10 00:00:00',
            'location' => 'foogoo',
            'available' => false,
            'state' => 'draft',
            'price' => '123.27',
            'opt_fields' => 'price',
        ];

        /**
         * We are checking here that when we book on 2021-09-09 19:00:00 to 2021-09-10 00:00:00, staff are available and
         * the correct price for the booking is returned.
         */
        $response = $this->get(route('api.products.get', $request), ['accept' => 'application/json']);
        $response->assertStatus(200)->assertJsonFragment([]);
    }

    /**
     * Test give me all active products!
     * Will need start and end dates due to special pricing date calculation.
     *
     * @return void
     */
    public function test_get_products_all_active_no_filters()
    {
        // As API user
        $this->actingAs($this->user, 'api');

        //With the test google distance calculator
        $this->app->bind(GoogleDistanceContract::class, MockGoogleDistance::class);

        $request = [
            'business' => $this->business->id,
            'state' => 'active',
            'opt_fields' => 'price',
        ];

        // Test fixed prices are returned.
        $this->product->price_type = 'fixed';
        $this->product->price_fixed_price = 225 * 100;
        $this->product->save();

        $response = $this->get(route('api.products.get', $request), ['accept' => 'application/json']);
        $response->assertJsonValidationErrors(['start', 'end']);
    }
}

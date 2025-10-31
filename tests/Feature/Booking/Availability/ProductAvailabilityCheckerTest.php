<?php

namespace Tests\Feature\Booking\Availability;

use App\Booking\Availability\Checker\AvailabilityData;
use App\Booking\Availability\Checker\AvailabilityRequest;
use App\Booking\Availability\Checker\AvailabilityResponse;
use App\Booking\Availability\Checker\Checker;
use App\Booking\Contracts\ScheduleManager;
use App\Booking\Product;
use App\Domains\Auth\Models\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Pnlinh\GoogleDistance\Contracts\GoogleDistanceContract;
use Tests\Feature\Booking\Availability\Mock\MockGoogleDistance;
use Tests\Feature\Booking\BookingTestDataGenerator;
use Tests\Feature\Booking\ProductGenerator;

class ProductAvailabilityCheckerTest extends BookingTestDataGenerator
{
    use ProductGenerator;

    /**
     * @var ScheduleManager
     */
    protected ScheduleManager $scheduleManager;

    /**
     * @var Checker
     */
    protected $checker;

    /**
     * Setup a dummy business and a product manager instance.
     *
     * @throws BindingResolutionException
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->business = $this->getBusiness();
        $this->user = User::factory()->create();
        $this->business->users()->attach($this->user->id);
        $this->scheduleManager = $this->app->make(ScheduleManager::class);
        $this->checker = new Checker();
        $this->product = $this->createDefaultServiceProduct($this->business);
        $this->importUserSchedule();
        $this->createAndAttachProductAndBusinessSchedule($this->product, $this->business);

        //Bind the test google distance calculator
        $this->app->bind(GoogleDistanceContract::class, MockGoogleDistance::class);
    }

    /**
     * Make an availability check for a given request.
     *
     * @param $requestData
     * @return AvailabilityData
     */
    protected function serviceRequest($requestData): AvailabilityData
    {
        $request = new AvailabilityRequest($requestData);
        $availabilityData = new AvailabilityData($request, new AvailabilityResponse());

        return $this->checker->checkAvailability($availabilityData);
    }

    /**
     * Test the availability checker
     *      Service product
     *      Within delivery distance.
     */
    public function testAvailabilityDeliveryServiceWithinDistance()
    {
        $result = $this->serviceRequest([
            'business' => $this->business,
            'product' => $this->product,
            'start' => '2021-06-14 1:30:00',
            'end' => '2021-06-14 14:00:00',
            'location' => 'ta93rs',
        ]);

        // Then the service is available to be delivered to me
        $this->assertEquals(AvailabilityResponse::STATUS_AVAILABLE, $result->response->status);
    }

    /**
     * Test the availability checker
     *      Service product
     *      Outside delivery distance - fail.
     */
    public function testAvailabilityDeliveryServiceOutsideDistance()
    {
        // When I check the availability
        $result = $this->serviceRequest([
            'business' => $this->business,
            'product' => $this->product,
            'start' => '2021-06-14 1:30:00',
            'end' => '2021-06-14 14:00:00',
            'location' => 'PL48LY',
        ]);

        // Then the service is NOT available to be delivered to me
        $this->assertEquals(AvailabilityResponse::STATUS_UNAVAILABLE, $result->response->status);
    }

    /**
     * Test the availability checker
     *      Service product
     *      Outside delivery distance - fail.
     */
    public function testAvailabilityDeliveryServiceOutsideTime()
    {
        $this->product->travelling_value = 20;
        $this->product->travelling_type = 'minutes';
        $this->product->save();

        // When I check the availability
        $result = $this->serviceRequest([
            'business' => $this->business,
            'product' => $this->product,
            'start' => '2021-06-14 1:30:00',
            'end' => '2021-06-14 14:00:00',
            'location' => 'ct170bs',
        ]);

        // The the service is available to be delivered to me
        $this->assertEquals(AvailabilityResponse::STATUS_UNAVAILABLE, $result->response->status);
    }

    /**
     * Test the availability checker
     *      Service product
     *      Product required 2 staff, only 1 available.
     */
    public function testAvailabilityNotEnoughStaffTwoRequiredOneAvailable()
    {
        $this->product->staff_quantity = 2;
        $this->product->save();

        $result = $this->serviceRequest([
            'business' => $this->business,
            'product' => $this->product,
            'start' => '2021-06-14 1:30:00',
            'end' => '2021-06-14 14:00:00',
            'location' => 'ta9 3rs',
        ]);

        // The the service is available to be delivered to me
        $this->assertEquals(AvailabilityResponse::STATUS_UNAVAILABLE, $result->response->status);
    }

    /**
     * Test 2 staff booking is available when 3 staff available.
     *      Service product
     *      Product required 2 staff, 2 are available.
     */
    public function testAvailabilityNotEnoughStaffTwoRequiredTwoAvailable()
    {
        //Given we have two staff
        $this->product->staff_quantity = 2;
        $this->product->save();

        $anotherUser = User::factory()->create();
        $this->business->users()->attach($anotherUser->id);

        // Given another staff a schedule
        $this->artisan('larabook:schedule-import', [
            'userid' => $anotherUser->id,
            'icsurl' => __DIR__.'/../../../random_day_test.ics',
        ]);

        // When we book
        $result = $this->serviceRequest([
            'business' => $this->business,
            'product' => $this->product,
            'start' => '2021-06-14 1:30:00',
            'end' => '2021-06-14 14:00:00',
            'location' => 'ta9 3rs',
        ]);

        // Then the service is available to be delivered to me
        $this->assertEquals(AvailabilityResponse::STATUS_AVAILABLE, $result->response->status);
    }

    /**
     * Test the availability checker: None available in stock.
     * @throws BindingResolutionException
     */
    public function testAvailabilityNoneInStock()
    {
        $this->product->available_quantity = 0;
        $this->product->type = Product::TYPE_PRODUCT;
        $this->product->save();

        $result = $this->serviceRequest([
            'business' => $this->business,
            'product' => $this->product,
            'start' => '2021-06-14 1:30:00',
            'end' => '2021-06-14 14:00:00',
            'location' => 'ta93rs',
            'quantity' => 1,
        ]);

        // The the service is available to be delivered to me
        $this->assertEquals(AvailabilityResponse::STATUS_UNAVAILABLE, $result->response->status);
    }

    /**
     * Test the availability checker: None available in stock.
     * @throws BindingResolutionException
     */
    public function testAvailabilityInStock()
    {
        $this->product->available_quantity = 3;
        $this->product->save();

        $result = $this->serviceRequest([
            'business' => $this->business,
            'product' => $this->product,
            'start' => '2021-06-14 1:30:00',
            'end' => '2021-06-14 14:00:00',
            'location' => 'ta93rs',
            'quantity' => 2,
        ]);

        // The the service is available to be delivered to me
        $this->assertEquals(AvailabilityResponse::STATUS_AVAILABLE, $result->response->status);
    }

    public function testAvailabilityProductInactive()
    {
        $this->product->state = Product::STATUS_DRAFT;
        $this->product->save();

        $result = $this->serviceRequest([
            'business' => $this->business,
            'product' => $this->product,
            'start' => '2021-06-14 1:30:00',
            'end' => '2021-06-14 14:00:00',
            'location' => 'ta93rs',
            'quantity' => 1,
        ]);

        // The the service is not available to be delivered to me
        $this->assertEquals(AvailabilityResponse::STATUS_UNAVAILABLE, $result->response->status);
    }

    public function testAvailabilityProductNotScheduled()
    {
        // Create new schedule for January
        $scheduleDetail = [
            'summary' => 'Test January 14th',
            'start_datetime' => '2021-01-14 0:00:00',
            'end_datetime' => '2021-01-15 00:00:00',
            'properties' => [
                'price' => 1500,
                'code' => 'Ummmm..',
            ],
        ];

        $schedule = $this->scheduleManager->create(
            $this->business,
            $scheduleDetail
        );

        $this->product->availability_type = 'scheduled';
        $this->product->availability_schedule = $schedule->id;

        //Try and book product when not scheduled in June
        $result = $this->serviceRequest([
            'business' => $this->business,
            'product' => $this->product,
            'start' => '2021-06-14 1:30:00',
            'end' => '2021-06-14 14:00:00',
            'location' => 'ta93rs',
            'quantity' => 1,
        ]);

        // The the service is available to be delivered to me
        $this->assertEquals(AvailabilityResponse::STATUS_UNAVAILABLE, $result->response->status);
    }

    public function testAvailabilityProductIdScheduled()
    {
        // Create new schedule for January
        $scheduleDetail = [
            'summary' => 'Test January 14th',
            'start_datetime' => '2023-01-21 0:00:00',
            'end_datetime' => '2023-01-22 00:00:00',
            'properties' => [
                'price' => 1500,
                'code' => 'Ummmm..',
            ],
        ];

        $schedule = $this->scheduleManager->create(
            $this->business,
            $scheduleDetail
        );

        $this->product->availability_type = 'scheduled';
        $this->product->availability_schedule = $schedule->id;
        $this->product->staff_quantity = 0; //Skip staff check.

        //Try and book product when not scheduled in June
        $result = $this->serviceRequest([
            'business' => $this->business,
            'product' => $this->product,
            'start' => '2023-01-21 1:30:00',
            'end' => '2023-01-21 14:00:00',
            'location' => 'ta93rs',
            'quantity' => 1,
        ]);

        // The the service is available to be delivered to me
        $this->assertEquals(AvailabilityResponse::STATUS_AVAILABLE, $result->response->status);
    }

    /**
     * @throws BindingResolutionException
     */
    public function testBookingChildProductChecksParentsStockLevels()
    {
        // Update the base parent product
        $this->product->available_quantity = 3;
        $this->product->type = Product::TYPE_PRODUCT;
        $this->product->save();

        $childProduct = Product::create([
            'name' => 'Child product 1 - no stock',
            'description' => 'a little funk',
            'state' => 'active',
            'sku' => 'what does sku mean?',
            'owner_type' => $this->business->getMorphClass(),
            'owner_id' => $this->business->id,
            'type' => Product::TYPE_PRODUCT,
            'available_quantity' => 0,
        ]);

        // Add it to the variations table
        $this->product->variations()->save($childProduct, [
            'stock_from_parent' => true,
        ]);

        $result = $this->serviceRequest([
            'business' => $this->business,
            'product' => $childProduct,
            'quantity' => 2,
        ]);

        $result = $this->serviceRequest([
            'business' => $this->business,
            'product' => $this->product,
            'quantity' => 2,
        ]);
        // The the service is available to be delivered to me
        $this->assertEquals(AvailabilityResponse::STATUS_AVAILABLE, $result->response->status);
    }

    public function testBookingChildProductDoesNotChecksParentsStockLevels()
    {
        $this->product->available_quantity = 3;
        $this->product->type = Product::TYPE_PRODUCT;
        $this->product->save();

        $result = $this->serviceRequest([
            'business' => $this->business,
            'product' => $this->product,
            'quantity' => 2,
        ]);

        // The the product is available to be delivered to me
        $this->assertEquals(AvailabilityResponse::STATUS_AVAILABLE, $result->response->status);
    }
}

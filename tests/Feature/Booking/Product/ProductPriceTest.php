<?php

use App\Booking\Availability\Checker\Checker;
use App\Booking\Contracts\ProductManager;
use App\Booking\Contracts\ScheduleManager;
use App\Booking\Product;
use App\Domains\Auth\Models\User;
use Carbon\Carbon;
use Tests\Feature\Booking\BookingTestDataGenerator;
use Tests\Feature\Booking\ProductGenerator;

class ProductPriceTest extends BookingTestDataGenerator
{
    use ProductGenerator;

    /**
     * @var ProductManager
     */
    private $productManager;

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

    public function test_calculate_product_price_fixed()
    {
        $fixedPrice = $this->product->getPrice([
            'start' => '2021-06-14 1:30:00',
            'end' => '2021-06-14 14:00:00',
        ]);

        $this->assertEquals(round(33.47), $fixedPrice);
    }

    public function test_calculate_product_advance_charge()
    {
        $data = [
            [
                "from" => "0",
                "to" => "14",
                "value" => "-15",
            ],
            [
                "from" => "365",
                "to" => "99999",
                "value" => "65.00",
            ]
        ];

        $this->product->settings->set('advance_charges', $data);

        $fixedPrice = $this->product->getPrice([
            'start' => Carbon::now()->addYear()->addDay(),
            'end' => Carbon::now()->addYear()->addDays(2),
        ]);
        $this->assertEquals(round(33.47 + 65.00), $fixedPrice);


        $fixedPrice = $this->product->getPrice([
            'start' => (string)\Illuminate\Support\Carbon::now(),
            'end' => (string)(\Illuminate\Support\Carbon::now()->addDay(1)),
        ]);
        $this->assertEquals(round(33.47 - 15.00), $fixedPrice);
    }

    public function test_calculate_product_charge_over_max_hours()
    {
        $start = \Illuminate\Support\Carbon::now();
        $this->product->settings->set('extra_hours_charge_max_hours', 5.5);

        $fixedPrice = $this->product->getPrice([
            'start' => (string)$start,
            'end' => (string)$start->addHours(5)->addMinutes(30),
        ]);

        $this->assertEquals(33.00, $fixedPrice);// 33.47 rounded

        $fixedPrice = $this->product->getPrice([
            'start' => (string)$start,
            'end' => (string)$start->addHours(5)->addMinutes(29)->addSeconds(15),
        ]);

        $this->assertEquals(/*33.47*/33.00, $fixedPrice);

        $fixedPrice = $this->product->getPrice([
            'start' => (string)$start,
            'end' => (string)$start->addHours(5)->addMinutes(31),
        ]);

        $pricePerHour = (1 / 5.5) * 33.47;
        $extraHours = 1/60;
        $expectedPrice = round(33.47 + ($extraHours * $pricePerHour));

        $this->assertEquals($expectedPrice, $fixedPrice);

    }

    public function test_calculate_product_price_hourly_scheduled()
    {
        $this->product->update(['price_type' => 'scheduled']);

        $schedule = $this->scheduleManager->create($this->business, [
            'summary' => 'Scheduled price - 1st June 2016 - 15th June 2016',
            'start_datetime' => '2021-07-14 0:00:00',
            'end_datetime' => '2021-07-15 00:00:00',
        ]);

        $this->product->schedules()->save($schedule, [
            'key' => 'hour',
            'value' => '2000', // £20.00 per hour
        ]);

        $fixedPrice = $this->product->getPrice([
            'start' => '2021-7-14 1:30:00',
            'end' => '2021-07-14 14:00:00',
        ]);

        $this->assertEquals(12.5 * 20.00, $fixedPrice);
    }

    public function test_calculate_product_price_daily_scheduled()
    {
        $this->product->update(['price_type' => 'scheduled']);

        $schedule = $this->scheduleManager->create($this->business, [
            'summary' => 'Scheduled price - 14th July 2021 - 15th July 2021',
            'start_datetime' => '2021-07-14 0:00:00',
            'end_datetime' => '2021-07-25 00:00:00',
        ]);

        $this->product->schedules()->save($schedule, [
            'key' => 'day',
            'value' => '20000', // £200.00 per day
        ]);

        // Check price for < 1 day
        $fixedPrice = $this->product->getPrice([
            'start' => '2021-7-14 1:30:00',
            'end' => '2021-07-14 14:00:00',
        ]);
        $this->assertEquals(200.00, $fixedPrice);

        // Check price for 3.5 days
        $fixedPrice = $this->product->getPrice([
            'start' => '2021-7-14 1:30:00',
            'end' => '2021-07-17 13:30:00',
        ]);
        $this->assertEquals(200.00 * 3.5, $fixedPrice);
    }

    public function test_calculate_product_price_special_scheduled()
    {
        $this->product->update(['price_type' => 'scheduled']);

        $schedule = $this->scheduleManager->create($this->business, [
            'summary' => 'Scheduled price - 14th July 2021 - 15th July 2021',
            'start_datetime' => '2021-07-14 0:00:00',
            'end_datetime' => '2021-07-25 00:00:00',
        ]);

        $this->product->schedules()->save($schedule, [
            'key' => 'special',
            'value' => (string)(599.99*100), // £599.99 per day
        ]);

        // Check price for < 1 day
        $fixedPrice = $this->product->getPrice([
            'start' => '2021-7-14 1:30:00',
            'end' => '2021-07-14 14:00:00',
        ]);
        $this->assertEquals(/*599.99*/600.00, $fixedPrice);

        // Check price for 3.5 days
        $fixedPrice = $this->product->getPrice([
            'start' => '2021-7-14 1:30:00',
            'end' => '2021-07-17 13:30:00',
        ]);
        $this->assertEquals(/*599.99*/600.00, $fixedPrice);
    }

    //@TODO fixed.
    public function test_calculate_product_price_special_fixed()
    {
        $this->product->update([
            'price_type' => 'fixed',
            'price_fixed_price' => 77.77*100,
        ]);

        $schedule = $this->scheduleManager->create($this->business, [
            'summary' => 'Scheduled price - 14th July 2021 - 15th July 2021',
            'start_datetime' => '2021-07-14 0:00:00',
            'end_datetime' => '2021-07-25 00:00:00',
        ]);

        $this->product->schedules()->save($schedule, [
            'key' => 'special',
            'value' => (string)(600.00*100), //599.99 £599.99 rounded to 600.00 per day
        ]);

        // Check price for < 1 day
        $fixedPrice = $this->product->getPrice([
            'start' => '2021-7-14 1:30:00',
            'end' => '2021-07-14 14:00:00',
        ]);
        $this->assertEquals(600.00, $fixedPrice);//599.99 rounded to 600

        // Check price for 3.5 days
        $fixedPrice = $this->product->getPrice([
            'start' => '2021-7-14 1:30:00',
            'end' => '2021-07-17 13:30:00',
        ]);
        $this->assertEquals(/*599.99*/600.00, $fixedPrice);
    }

    public function test_calculate_product_price_per_booking()
    {
        $this->product->update(['price_type' => 'scheduled']);

        $schedule = $this->scheduleManager->create($this->business, [
            'summary' => 'Scheduled price - 14th July 2021 - 15th July 2021',
            'start_datetime' => '2021-07-14 0:00:00',
            'end_datetime' => '2021-07-25 00:00:00',
        ]);

        $this->product->schedules()->save($schedule, [
            'key' => 'booking',
            'value' => '12600', // £125.55 per booking rounded 12600
        ]);

        // Check price for < 1 day
        $fixedPrice = $this->product->getPrice([
            'start' => '2021-7-14 1:30:00',
            'end' => '2021-07-14 14:00:00',
        ]);
        $this->assertEquals(/*125.55*/ 126.00, $fixedPrice);

        // Check price for 3.5 days
        $fixedPrice = $this->product->getPrice([
            'start' => '2021-7-14 1:30:00',
            'end' => '2021-07-17 13:30:00',
        ]);
        $this->assertEquals(/*125.55*/ 126.00, $fixedPrice);
    }

    public function test_calculate_product_price_missing_params()
    {
        $this->product->update(['price_type' => 'scheduled']);
        $this->expectException(Product\Exceptions\ProductPriceNotAvailable::class);
        $this->expectExceptionMessage('For pricing, please enter start and end dates');
        $this->product->getPrice([
            'start' => '2021-7-14 1:30:00',
        ]);
    }

    public function test_calculate_product_price_outside_schedule()
    {
        $this->product->update(['price_type' => 'scheduled']);

        $schedule = $this->scheduleManager->create($this->business, [
            'summary' => 'Scheduled price - 1st June 2016 - 15th June 2016',
            'start_datetime' => '2021-07-14 0:00:00',
            'end_datetime' => '2021-07-15 00:00:00',
        ]);

        $this->product->schedules()->save($schedule, [
            'key' => 'hour',
            'value' => '2000', // £20.00 per hour
        ]);

        $this->expectException(\App\Booking\Product\Exceptions\ProductPriceNotAvailable::class);
        $this->expectExceptionMessage('Product price is not currently available');

        //Attempt to find a price for 2022, which we haven't configured!!
        $this->product->getPrice([
            'start' => '2022-7-14 1:30:00',
            'end' => '2022-07-14 14:00:00',
        ]);
    }

    public function test_product_price_starts_within_schedule()
    {
        $this->product->update(['price_type' => 'scheduled']);

        $schedule = $this->scheduleManager->create($this->business, [
            'summary' => 'Scheduled price - 1st June 2016 - 15th June 2016',
            'start_datetime' => '2021-07-14 0:00:00',
            'end_datetime' => '2021-07-15 00:00:00',
        ]);

        $this->product->schedules()->save($schedule, [
            'key' => 'hour',
            'value' => '2000', // £20.00 per hour
        ]);

        //Price starts within schedule.
        $price = $this->product->getPrice([
            'start' => '2021-07-14 20:00:00',
            'end' => '2021-07-15 01:00:00',
        ]);

        $this->assertEquals(5*20, $price);
    }

    public function test_product_price_starts_within_schedule_direct()
    {
        $this->product->update(['price_type' => 'scheduled']);

        $schedule = $this->scheduleManager->create($this->business, [
            'summary' => 'Scheduled price - 1st June 2016 - 15th June 2016',
            'start_datetime' => '2021-07-12 00:00:00',
            'end_datetime' => '2021-07-15 00:00:00',
        ]);

        $this->product->schedules()->save($schedule, [
            'key' => 'hour',
            'value' => '2000', // £20.00 per hour
        ]);

        //Price starts within schedule.
        $this->assertIsObject(
            $this->product->scheduledStarting(
                toCarbon('2021-07-14 20:00:00'),
                toCarbon('2021-07-15 01:00:00'),
            )
        );

        $this->assertIsObject(
            $this->product->scheduledStarting(
                toCarbon('2021-07-13 20:00:00'),
                toCarbon('2021-07-14 01:00:00'),
            )
        );

        // Price starts outside of schedule (after)
        $this->assertNull(
            $this->product->scheduledStarting(
                toCarbon('2021-07-15 20:00:00'),
                toCarbon('2021-07-16 01:00:00'),
            )
        );

        // Price starts outside of schedule (before)
        $this->assertNull(
            $this->product->scheduledStarting(
                toCarbon('2021-07-11 20:00:00'),
                toCarbon('2021-07-12 01:00:00'),
            )
        );
    }
}

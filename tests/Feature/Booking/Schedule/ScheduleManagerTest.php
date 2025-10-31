<?php

namespace Tests\Unit\Holiday;

use App\Booking\Availability\Schedule;
use App\Booking\Business;
use App\Booking\Contracts\ScheduleManager;
use App\Booking\Holiday;
use App\Booking\Models\Calendar;
use App\Booking\Product;
use App\Domains\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleManagerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var ScheduleManager
     */
    private $scheduleManager;

    /**
     * @var User
     */
    private $staff;

    /**
     * @var Business
     */
    private $business;

    public function setUp(): void
    {
        parent::setUp();
        $this->scheduleManager = $this->app->make(ScheduleManager::class);
        $this->staff = User::create([
            'name' => 'Schedule Manager Test',
            'email' => 'scheduleManager@kwikevent.com',
            'password' => 1234,
        ]);

        $this->business = $this->getBusiness();
    }

    /**
     * Test create a single one off event booking staff availability.
     *
     * @throws \Exception
     */
    public function testCreateSingleEventStaffAvailability()
    {
        $testData = [];
        for ($i = 0; $i <= 1; $i++) {
            $testData[$i] = [
                'summary' => 'Test single',
                'start_datetime' => new \DateTime(),
                'end_datetime' => new \DateTime(),
                'properties' => [
                    'price' => 1500,
                    'code' => '50%off',
                ],
            ];

            $this->scheduleManager->createSingleEvent($this->staff, $testData[$i]);
        }

        $availability = $this->staff->availability();
        $count = $availability->count();
        $first = $availability->first();

        $this->assertInstanceOf(Schedule::class, $first);
        $this->assertEquals(2, $count);

        $this->scheduleManager->clearSchedule($this->staff);

        $count = $availability->count();
        $this->assertEquals(0, $count);
    }

    /**
     * Test the product <> schedule mapping.
     *
     * The schedule is used for scheduled pricing and lives in schedule_maps
     *
     *  Schedule: products() { return $this->morphedByMany(Product::class, 'schedule_map');}
     *  Product: schedules() { return $this->morphToMany(Schedule::class,'schedule_map')->withPivot(['key', 'value']);}
     *  AnotherModel: schedules() { return $this->morphToMany(Schedule::class,'schedule_map')->withPivot(['key', 'value']);}
     *
     *  Automagically: saves a relationship into the table schedule_map (as defined above). Table columns are automatically
     *      calculated from table name etc...
     *
     * As I use this a lot and often get these mappings wrong, I will use this as a simple template.
     */
    public function testProductScheduleMap()
    {
        $scheduleDetail = [
            'summary' => 'Test single #123123',
            'start_datetime' => new \DateTime(),
            'end_datetime' => new \DateTime(),
            'properties' => [
                'price' => 1500,
                'code' => '50%off',
            ],
        ];
        $schedule = $this->scheduleManager->create($this->business, $scheduleDetail);

        $product = Product::create([
            'name'  => 'Bouncy castle blower hire',
            'sku'   => 'BOUNCY BOUNCY!',
            'price' => 17.95,
            'type' => Product::class,
            'owner_id' => $this->business->id,
            'owner_type' => $this->business->getMorphClass(),
        ]);

//      Replaced by save() - Will leave this here  as a brief reminder of how to manage polymorphic relationships
//      Keyword: Satsuma
//
//        $scheduleMap = ScheduleMap::make([
//            'schedule_map_id' => $product->id,
//            'schedule_map_type' => $product->getMorphClass(),
//            'schedule_id' => $schedule->id,
//            'key' => 'booking',
//            'value' => '220.00',
//        ]);

        $product->schedules()->save($product, [
            'key' => 'booking',
            'value' => '220.00',
        ]);

        $morphToMany = $product->schedules();
        $schedule = $morphToMany->first();

        // Check Product -> schedule
        $this->assertEquals(1, $morphToMany->count());
        $this->assertEquals(1, $schedule->id);
        $this->assertEquals('business', $schedule->model_type);
        $this->assertEquals(1, $schedule->model_id);
        $this->assertEquals('Test single #123123', $schedule->summary);
        $this->assertEquals('booking', $schedule->pivot->key);
        $this->assertEquals('220', $schedule->pivot->value);

        //Check schedule <- product
        $schedule->refresh();
        $product_related = $schedule->products()->first();
        $this->assertEquals('Bouncy castle blower hire', $product_related->name);
    }

    //Test clear holiday
    public function testClearHoliday()
    {
        /**
         * @var User $staff
         */
        $staff = User::create(['name' => 'test', 'email' => 'test@test.com', 'password' =>1234]);

        // With a holiday
        $holiday = Holiday::create([
            'title' => 'A.Test "" {} [][\\n',
            'description' => "some <b>Description's\n\nTest",
        ]);

        // When the staff has a booking...
        $bookingCalendarEntry = Calendar::create([
            'model_id' => $staff->id,
            'model_type' => $staff->getMorphClass(),
            'booked_by_type' => $holiday->getMorphClass(),
            'booked_by_id' => $holiday->id,
            'start' => '2020-04-18 14:00:00',
            'end' => '2020-04-19 1:00:00',
        ]);
        $bookingCalendarEntry = Calendar::create([
            'model_id' => $staff->id,
            'model_type' => $staff->getMorphClass(),
            'booked_by_type' => $holiday->getMorphClass(),
            'booked_by_id' => $holiday->id,
            'start' => '2021-05-18 12:00:00',
            'end' => '2021-05-19 1:00:00',
        ]);

        $this->assertEquals(2, $staff->calendar()->hols()->with('booked_by')->count());
        $this->scheduleManager->clearHoliday($staff);
        $this->assertEquals(0, $staff->calendar()->hols()->with('booked_by')->count());
    }
}

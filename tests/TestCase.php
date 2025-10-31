<?php

namespace Tests;

require_once 'EmailHelper.php';

use App\Booking\Business;
use App\Booking\Product;
use App\Domains\Auth\Http\Middleware\TwoFactorAuthenticationStatus;
use App\Domains\Auth\Models\Role;
use App\Domains\Auth\Models\User;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Konekt\Address\Models\Address;
use Konekt\Address\Models\AddressType;
use Konekt\Address\Models\Country;
use Pnlinh\GoogleDistance\Contracts\GoogleDistanceContract;
use Tests\EmailHelperTrait;
use Tests\Feature\Booking\Availability\Mock\MockGoogleDistance;

/**
 * Class TestCase.
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    use EmailHelperTrait;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed');

        $this->withoutMiddleware(RequirePassword::class);
        $this->withoutMiddleware(TwoFactorAuthenticationStatus::class);

        //Bind the test google distance calculator
        $this->app->bind(GoogleDistanceContract::class, MockGoogleDistance::class);

        config()->set('testing.allow_past_bookings', true);
        config()->set('testing.allow_duplicate_events_to_be_added', true);
    }

    protected function createCountries()
    {
//        static $count = 0;
//        $count++;

//        Country::create([
//            'id'           => 'DE',
//            'name'         => 'Germany',
//            'phonecode'    => 49,
//            'is_eu_member' => 1, ]);

//        Country::create([
//            'id'           => 'UK',
//            'name'         => 'United Kingdom', // TODO - remove and fix tests!
//            'phonecode'    => 44,
//            'is_eu_member' => 1,
//        ]);

//        Country::create([
//            'id'           => 'GB',
//            'name'         => 'United Kingdom (as per live)',
//            'phonecode'    => 44,
//            'is_eu_member' => 1,
//        ]);
    }

    protected function getBusinessAddress()
    {
       // dd(Country::where('id', 'UK')->first()->toJson(JSON_PRETTY_PRINT));
       // $this->createCountries();

        return factory(Address::class)->create([
            'type' => AddressType::BUSINESS,
            'name' => 'Business address',
            'address' => '14 Grove Road, west huntspill',
            'city' => 'somerset',
            'postalcode' => 'TA93RS',
            'country_id' => 'GB',
        ]);
    }

    protected function getBusiness($name = 'Elite Booking Systems')
    {
        $address = $this->getBusinessAddress();

        $business =  factory(Business::class)->create([
            'name' => $name,
            'address_id' => $address->id,
            'timezone' => 'UTC',
            'currency' => 'GBP'
        ]);

        $this->setupTestEmails($business);

        return $business;
    }

    /**
     *  Import a user schedule
     * DTSTART;VALUE=   DATE:2020-09-09
     * DTEND;VALUE=     DATE:2020-09-10
     * RRULE:FREQ=YEARLY.
     *
     * DTSTART;TZID=Europe/London:2020-10-10 T 06:00:00
     * DTEND;TZID=Europe/London:  2020-10-17 T 02:00:00
     * RRULE:FREQ=YEARLY
     *
     * DTSTART;TZID=Europe/London:  2020-11-11 T 12:00:00
     * DTEND;TZID=Europe/London:    2020-11-11 T 14:30:00
     * RRULE:FREQ=YEARLY
     */
    public function importUserSchedule()
    {
        $pendingCommand = $this->artisan('larabook:schedule-import', [
            'userid' => $this->user->id,
            'icsurl' => __DIR__.'/random_day_test.ics',
            //{--tag=[holiday]}';
        ]);
        $pendingCommand->assertExitCode(0);
        $pendingCommand->execute();
    }

    /**
     * Create and attach a schedule to a business, then a product.
     *
     * @param Product $product
     * @param Business $business
     * @return \App\Booking\Availability\Schedule
     * @throws \Exception
     */
    public function createAndAttachProductAndBusinessSchedule(Product $product, Business $business)
    {
        $scheduleDetail = [
            'summary' => 'Test product single #123123',
            'start_datetime' => '2021-06-14 0:00:00',
            'end_datetime' => '2021-06-15 00:00:00',
            'properties' => [
                'price' => 1500,
                'code' => '50%off',
            ],
        ];
        $schedule = $this->scheduleManager->create($business, $scheduleDetail);

        $product->schedules()->save($schedule, [
            'key' => 'booking',
            'value' => '220.00',
        ]);

        return $schedule;
    }

    protected function getAdminRole()
    {
        return Role::find(1);
    }

    protected function getMasterAdmin()
    {
        return User::find(1);
    }

    protected function loginAsAdmin($admin = false)
    {
        if (! $admin) {
            $admin = $this->getMasterAdmin();
        }

        $this->actingAs($admin);

        return $admin;
    }

    protected function logout()
    {
        return auth()->logout();
    }
}

<?php

namespace Tests\Feature\Booking;

use App\Booking\Business;
use App\Booking\Customer;
use App\Booking\Product;
use App\Domains\Auth\Models\User;
use App\Domains\Form\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Konekt\Address\Models\Address;
use Konekt\Address\Models\AddressType;
use Konekt\Address\Models\Country;
use Tests\TestCase;

class BookingTestDataGenerator extends TestCase
{
    //use RefreshDatabase;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var User
     */
    protected $customer;

    /**
     * @var Business
     */
    protected $business;

    /**
     * @var array
     */
    protected $staffScheduleData = [];

    /**
     * @var Country
     */
    protected $country;

    /**
     * @var Address
     */
    protected $address;

    /**
     * @var User
     */
    protected $user;

    public function createFixedPriceServiceProduct(Business $business)
    {
        $this->product = Product::create([
            'owner_id' => $business->id,
            'owner_type' => Business::class,
            'name' => 'Elite booking systems',
            'description' => 'Hire of booking system',
            'state' => 'active',
            'sku' => 'booking-october-2020',
            'slug' => 'Ewww I hate slugs!',
            'url' => 'https://elitebookingsystem.com',

//            'setup_time' => 45,

            'type' =>'service',
            'price_type' => 'fixed',
            'price_fixed_price' => 3347,
            'staff_quantity' => 12,
            'availability_type' => 'available',
            //'availability_schedule' => 1,
            'available_quantity' => 1,
            'travelling_limit' => 'yes',
            'travelling_value' => 12.65,
            'travelling_type' => 'miles',
            'delivery_method' => Product::DELIVERY_METHODS['delivered'] | Product::DELIVERY_METHODS['collected'],
            'delivery' => [
                'delivered' => [
                    'charge' => 0.12,
                    'per' => 'mile',
                ],
                'shipped' => [
                    'price' => 10.00,
                    'per' => 'order',
                ],
            ],
        ]);
    }

    /**
     * Create customer with data.
     */
    public function createCustomerData()
    {
        $this->customer = Customer::factory()->create();
        $this->address = factory(Address::class)->create(['type' => AddressType::SHIPPING]);
    }

    /**
     * Create business with 2 staff and staff schedules.
     *
     * @return Business
     */
    public function createBusinessWithData(): Business
    {
        $this->business = $this->getBusiness('50 quid sidney\'s');

        $staff1 = User::factory()->create(['name' => 'staff1']);
        $staff2 = User::factory()->create(['name' => 'staff2']);

        //Attach staff to business.
        $this->business->users()->attach($staff1);
        $this->business->users()->attach($staff2);

        // Give members of staff weekend availability.
        $this->createStaffScheduleTestData($staff1);

        return $this->business;
    }

    /**
     * Import a test calendar with rrules configured for:.
     *
     *  Available:
     *      Monday to Friday after 5:30pm until 2am
     *      Saturday 00:00:00 until Monday 2am
     *
     * @param $staff
     * @param string $url
     */
    protected function createStaffScheduleTestData($staff, $url = '/mon-fri-530-2am--sat-sun.ics')
    {

        // When we run the import cli command
        $pendingCommand = $this->artisan('larabook:schedule-import', [
            'userid' => $staff->id,
            'icsurl' => __DIR__.$url,
            //{--tag=[holiday]}';
        ]);
        $pendingCommand->assertExitCode(0);
        $pendingCommand->execute();
    }
}

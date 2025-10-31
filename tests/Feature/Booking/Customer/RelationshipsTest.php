<?php

namespace Tests\Feature\Customer;

use App\Booking\Business;
use App\Booking\Customer;
use App\Domains\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Vanilo\Order\Models\Order;

class RelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that Business can atttach staff.
     *
     * Test the relationship between business and customers
     */
    public function testBusinessCanAttachStaff()
    {
        $businessName = '50 quid sid';
        $staff = User::create(['name' => 'test', 'email' => 'test@test.com', 'password' =>1234]);

        //Check user creeated ok [yes I do need to do this as I was having a bug with default properties and global scopes]
        $this->assertNotNull($staff);

        $business = $this->getBusiness($businessName);
        $business->users()->attach($staff);

        $fetched = Business::with('users')->first();
        $this->assertEquals($fetched->users->first()->email, 'test@test.com');
    }

    /**
     * Test that Customers can attach orders.
     *
     * Test the relationship between business and customers
     */
    public function testCustomersCanAttachOrders()
    {

        //$customer = factory(Customer::class)->create();
        $customer = Customer::factory()->create();
        $order = factory(Order::class, 4);
        $order->create([
            'location' => 'a wooden shack in the woods',
            'start' => '2020-01-01 00:00:00',
            'end' => '2020-01-01 00:00:00',
            'business_id' => 1,
            'user_id' => $customer->id,
        ]);

        $orders = $customer->orders()->get();
        $this->assertCount(4, $orders);
    }
}

<?php

namespace Tests\Feature\Booking\Booking;

use App\Booking\Address;
use App\Booking\Models\Calendar;
use App\Booking\Services\OrderManager;
use App\Domains\Auth\Models\User;
use App\Domains\Billpayer\Models\Billpayer;
use App\Domains\Booking\Models\Booking;
use App\Domains\Form\Models\FormResponses;
use App\Domains\Order\Order;
use Vanilo\Order\Models\OrderItem;

class DeleteBookingTest extends CreateBookingTest
{
    public function test_order_is_deleted()
    {

        // Get initial count of items so that we can later verify that created
        // items have been deleted.
        $quantityAddresses = Address::count();
        $quantityBillpayer = Billpayer::count();
        $quantityOrderItems = OrderItem::count();
        $quantityOrders = Order::count();
        $quantityFormResponses = FormResponses::count();
        $quantityBookings = Booking::count();
        $quantityCalendarEntries = Calendar::count();
        $quantityUsers = User::count();

        // Build cart, business, user, products and place an order.
        $this->order_can_be_created_with_minimal_data();

        // Assuming there is only 1 order.
        $oder = Order::first();

        // Delete the order.
        $orderManager = resolve(OrderManager::class);
        $orderManager->delete($oder);

        // Base test order_can_be_created_with_minimal_data creates a business which
        // has an address, so we increment the expected value.
        $quantityAddresses = $quantityAddresses + 1;

        // Base test order_can_be_created_with_minimal_data creates 2 staff users
        // so we increment the expected value.
        // A new User Customer will be crfeated and not deleted, so total is 3
        $quantityUsers = $quantityUsers + 3;

        // Then expect the items to be deleted (soft or hard)
        $this->assertEquals($quantityAddresses, Address::count());
        $this->assertEquals($quantityBillpayer, Billpayer::count());
        $this->assertEquals($quantityOrderItems, OrderItem::count());
        $this->assertEquals($quantityOrders, Order::count());
        $this->assertEquals($quantityFormResponses, FormResponses::count());
        $this->assertEquals($quantityBookings, Booking::count());
        $this->assertEquals($quantityCalendarEntries, Calendar::count());
        $this->assertEquals($quantityUsers, User::count());
    }
}

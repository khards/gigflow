<?php

namespace Database\Factories\Domains\Order;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Konekt\Address\Models\Address;
use Vanilo\Order\Models\Billpayer;

//use App\Domains\Checkout\CustomerForm\Address;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Domains\Order\Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $billpayer = factory(Billpayer::class)->create();
        $user = factory(User::class)->create();
        $address = factory(Address::class)->create();

        return [
            'number' => rand(100000, 900000),
            'billpayer_id' => $billpayer->id,
            'user_id' => $user->id,
            'status'              => \App\Domains\Order\OrderStatus::NEW(),
            'shipping_address_id' => $address->id,
            'dispatchPrice'       => 0,
            'totalProductPrice'   => 0,
            'adjustments'         => 0,
            'totalPrice'          => 0,
            'deposit'             => 0,
            'start'               => '1999-07-11 19:00:00',
            'end'                 => '1999-07-12 00:00:00',
            'business_id'         => 23,
            'location'            => 'Newquay, Cornwall',
        ];
    }

    public function withBusiness($business): OrderFactory
    {
        return $this->state(function (array $attributes) use ($business) {
            return [
                'business_id' => $business->id,
            ];
        });
    }

//    public function withOderItems(array $items)
//    {
//        return $this->state(function (array $attributes) use ($items) {
//            return [
//                'business_id' => $business->id,
//            ];
//        });
//    }
}

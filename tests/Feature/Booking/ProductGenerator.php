<?php

namespace Tests\Feature\Booking;

use App\Booking\Business;
use App\Booking\Contracts\ProductManager;
use App\Booking\Product;

trait ProductGenerator
{
    /**
     * @param Business $business
     * @return \App\Booking\Product|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createDefaultServiceProduct($business)
    {
        $productManager = app()->make(ProductManager::class);

        $imageFactory = new \Illuminate\Http\Testing\FileFactory();
        $image = $imageFactory->create('Test file', 50, 'png');

        // Given I want to update the fields
        $updatedProductData = [
            'name' => 'Elite booking systems',
            'description' => 'Hire of booking system',
            'state' => 'active',
            'sku' => 'booking-october-2020',
            'slug' => 'Ewww I hate slugs!',
            'image' => $image,
            'url' => 'https://elitebookingsystem.com/',

            'type' => 'service',
            'price_type' => 'fixed',
            'price_fixed_price' => 3347,
            'staff_quantity' => 1,
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
        ];

        // Then I update the product
        return $productManager->create($business, $updatedProductData);
    }
}

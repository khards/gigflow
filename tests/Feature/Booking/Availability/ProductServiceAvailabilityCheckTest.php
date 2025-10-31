<?php

use App\Booking\Contracts\ProductManager;
use App\Booking\Product;

class ProductServiceAvailabilityCheckTest extends \Tests\Feature\Booking\Availability\ProductAvailabilityCheckerTest
{
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
    }

    private function generateAvailabilityTestProductData()
    {
        // Given I have the following base product(s)
        $productData = [
            'name' => 'Elite booking systems',
            'description' => 'Hire of booking system',
            'state' => 'active',
            'sku' => 'booking-october-2020',
            'slug' => 'Ewww I hate slugs!',
            'url' => 'https://elitebookingsystem.com',

            'type' =>'service',
            'price_type' => 'fixed',
            'price_fixed_price' => 3347,
            'staff_quantity' => 12,
            'availability_type' => 'scheduled',
            'availability_schedule' => 1,
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

        // Product 1 is bookable and exists
        $product1 = $this->product;

        // Create product 2 & it's schedule (No Staff not available)
        $product2 = $this->productManager->create(
            $this->business->id,
            $productData
        );
        $scheduleDetail = ['summary' => 'Test product 2', 'start_datetime' => '2020-06-14 0:00:00', 'end_datetime' => '2022-12-15 00:00:00'];
        $schedule = $this->scheduleManager->create($this->business, $scheduleDetail);
        $product2->schedules()->save($schedule);

        // Product 3 is draft (Error: Not active)
        $product3 = $this->productManager->create(
            $this->business->id,
            array_merge($productData, ['name' => 'Elite booking systems (draft)', 'state' => 'draft'])
        );

        // Product 4 is outside the travelling distance
        $product4 = $this->productManager->create(
            $this->business->id,
            array_merge($productData, [
                'name' => 'Elite booking systems (1 miles)',
                'travelling_value' => 1,
            ])
        );
        $product4->schedules()->save($schedule);
    }

    public function test_get_all_active_products_for_business_within_travelling()
    {
        $this->generateAvailabilityTestProductData();

        $productManager = app()->make(ProductManager::class);
        $availabilityData = $productManager->all($this->business->id, [
            'state' => 'active',
            'available' => true,
            'location' => 'TA64RN',
            'start' => '2021-06-14 1:30:00',
            'end' => '2021-06-14 14:00:00',
        ]);

        $this->assertCount(1, $availabilityData['available']);
        $this->assertEquals('available', $availabilityData['available'][0]->response->status);
    }

    public function test_get_all_active_products_for_business_no_dates_or_location()
    {
        $this->generateAvailabilityTestProductData();

        $productManager = app()->make(ProductManager::class);

        // We omit dates and location so that staff and availability checks are skipped.
        $availabilityDatas = $productManager->all($this->business->id, [
            'state' => 'active',
            'available' => true,
        ]);

        $sorted = ['available' => [], 'unavailable' => []];
        foreach ($availabilityDatas['available'] as $availabilityData) {
            if ($availabilityData->response->status == 'available') {
                $sorted['available'][$availabilityData->request->product->id] = true;
            } elseif ($availabilityData->response->status == 'unavailable') {
                $sorted['unavailable'][$availabilityData->request->product->id] = true;
            }
        }
        $this->assertCount(3, $sorted['available']);
        $this->assertArrayHasKey(1, $sorted['available']);
        $this->assertArrayHasKey(2, $sorted['available']);
        $this->assertArrayHasKey(4, $sorted['available']);

        $this->assertCount(0, $sorted['unavailable']);
    }

    public function test_get_all_products_no_filters()
    {
        $this->generateAvailabilityTestProductData();

        $productManager = app()->make(ProductManager::class);

        // We omit dates and location so that staff and availability checks are skipped.
        $availabilityDatas = $productManager->all($this->business->id);

        $availabilityDatas = array_merge($availabilityDatas['available'], $availabilityDatas['unavailable']);

        $this->assertCount(4, $availabilityDatas);

        $available = [];
        $unavailable = [];
        foreach ($availabilityDatas as $availabilityData) {
            if ($availabilityData->response->status == 'available') {
                $available[$availabilityData->request->product->id] = true;
            } elseif ($availabilityData->response->status == 'unavailable') {
                $unavailable[$availabilityData->request->product->id] = true;
            }
        }
        $this->assertCount(3, $available);
        $this->assertArrayHasKey(1, $available);
        $this->assertArrayHasKey(2, $available);
        $this->assertArrayHasKey(4, $available);

        $this->assertCount(1, $unavailable);
        $this->assertArrayHasKey(3, $unavailable);
    }
}

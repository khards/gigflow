<?php

namespace Tests\Feature\Booking\Product;

use App\Booking\Business;
use App\Booking\Contracts\ProductManager;
use App\Booking\Product;
use Tests\TestCase;

/**
 * This is for testing the Product Service.
 *
 * Could make post/get requests to test this!
 * A service would be handy for anything NOT using this front end product controller!
 *
 * Class ProductServiceTest
 */
class ProductServiceTest extends TestCase
{
    /**
     * @var Business
     */
    private $business;

    /**
     * @var ProductManager
     */
    private $productManager;

    // Given some product data
    public static $productData = [
        'name' => 'ebay KFC powder 450g',
        'description' => 'Some good ol fried chickn powder',
        'state' => 'active',
        'sku' => 'fried-chkn-450g',
        'delivery_method' => Product::DELIVERY_METHODS['delivered'] | Product::DELIVERY_METHODS['collected'],
    ];

    /**
     * Setup a dummy business and a product manager instance.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->business = $this->getBusiness();
        $this->productManager = app()->make(ProductManager::class);
    }

    /**
     * Create a standard test product.
     *
     * @return Product|mixed
     */
    private function createTestProduct()
    {

        //When I create a product
        return $this->productManager->create(
            $this->business->id,
            static::$productData
        );
    }

    /**
     * Test product manager can create a product from product data.
     */
    public function test_product_can_be_created()
    {
        $product = $this->createTestProduct();

        // Then I have the correct product info returned.
        $this->assertEquals(static::$productData['name'], $product->name);
        $this->assertEquals(static::$productData['description'], $product->description);
        $this->assertEquals(static::$productData['state'], $product->state);
        $this->assertEquals(static::$productData['sku'], $product->sku);
        $this->assertEquals($this->business->id, $product->business->id);
    }

    public function test_product_can_be_updated()
    {
        // Given I have a dummy product
        $product = $this->createTestProduct();

        //$image = UploadedFile::fake();
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
            'url' => 'https://elitebookingsystem.com',

            'type' =>'service',
            'price_type' => 'fixed',
            'setup_time' => 16,
            'price_fixed_price' => 3347,
            'staff_quantity' => 12,
            'availability_type' => 'scheduled',
            'availability_schedule' => 1,
            'available_quantity' => 1,
            'travelling_limit' => 'yes',
            'travelling_value' => 12.65,
            'travelling_type' => 'miles',
            'form' => 231,
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
        $this->productManager->update($product, $updatedProductData);

        // Then check the product is updated.

        $this->assertEquals($updatedProductData['name'], $product->name);
        $this->assertEquals($updatedProductData['description'], $product->description);
        $this->assertEquals($updatedProductData['state'], $product->state);
        $this->assertEquals($updatedProductData['sku'], $product->sku);
        $this->assertEquals($updatedProductData['setup_time'], $product->setup_time);
        $this->assertEquals($this->business->id, $product->business->id);
        $this->assertEquals($updatedProductData['slug'], $product->slug);
        $this->assertEquals($updatedProductData['type'], $product->type);
        $this->assertEquals($updatedProductData['price_type'], $product->price_type);
        $this->assertEquals($updatedProductData['price_fixed_price'], $product->price_fixed_price);
        $this->assertEquals($updatedProductData['staff_quantity'], $product->staff_quantity);
        $this->assertEquals($updatedProductData['availability_type'], $product->availability_type);
        $this->assertEquals($updatedProductData['availability_schedule'], $product->availability_schedule);
        $this->assertEquals($updatedProductData['available_quantity'], $product->available_quantity);
        $this->assertEquals($updatedProductData['travelling_limit'], $product->travelling_limit);
        $this->assertEquals($updatedProductData['travelling_value'], $product->travelling_value);
        $this->assertEquals($updatedProductData['travelling_type'], $product->travelling_type);
        $this->assertEquals($updatedProductData['form'], $product->form_id);

        // Then check the product Settings are updated.
        $this->assertEquals($updatedProductData['url'], $product->settings->get('url'));
        $this->assertStringContainsString($image->hashName(), $product->settings->get('image_path'));
        $this->assertEquals(
            $updatedProductData['delivery']['delivered']['charge'],
            $product->settings->get('delivery.delivered.charge')
        );
        $this->assertEquals(
            $updatedProductData['delivery']['delivered']['per'],
            $product->settings->get('delivery.delivered.per')
        );
        $this->assertEquals(
            $updatedProductData['delivery']['shipped']['price'],
            $product->settings->get('delivery.shipped.price')
        );
        $this->assertEquals(
            $updatedProductData['delivery']['shipped']['per'],
            $product->settings->get('delivery.shipped.per')
        );
    }

    public function test_product_can_be_deleted()
    {
        $productManager = app()->make(ProductManager::class);
        $this->createTestProduct();
        $product = Product::first();
        $result = $productManager->delete($product->id);
        $this->assertTrue($result);
        //assert image was deleted.
    }

    public function test_get_all_products_for_business()
    {
        $this->productManager->create(
            $this->business->id,
            static::$productData
        );

        $this->productManager->create(
            $this->business->id,
            static::$productData
        );
        $productManager = app()->make(ProductManager::class);
        $products = $productManager->all($this->business->id);

        $this->assertCount(2, $products['available']);
        $this->assertCount(0, $products['unavailable']);
    }

    public function test_get_all_active_products_for_business()
    {
        $product1 = $this->productManager->create(
            $this->business->id,
            static::$productData
        );

        $product2 = $this->productManager->create(
            $this->business->id,
            array_merge(static::$productData, ['state' => 'draft'])
        );

        $productManager = app()->make(ProductManager::class);

        $availabilityDatas = $productManager->all($this->business->id, [
            'state' => 'active',
        ]);

        $this->assertCount(1, $availabilityDatas['available']);
        $this->assertCount(1, $availabilityDatas['unavailable']);
    }
}

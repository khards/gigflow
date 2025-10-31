<?php

namespace Tests\Feature\Booking\Product;

use App\Booking\Business;
use App\Booking\Contracts\ProductManager;
use App\Booking\Product;
use App\Domains\Auth\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * This is for testing the Product front end Controller.
 *
 * This uses post and get requests to test
 *
 * Class ProductControllerTest
 */
class ProductVariationTest extends ProductControllerTest
{
    /**
     * @var Product
     */
    protected $product;

    public function test_product_variations_can_be_updated_on_save()
    {
        $this->test_product_variation_can_be_created();

        // Post an update existing variation / edit product screen
        $response = $this->post(route('frontend.user.product.edit', $this->product->id), [
            'product_name'  => 'Homer simpson',
            'product_description'  => 'Main simpsons character',
            'delivery_methods'  => ['delivered'],
            'price_type'  => 'fixed',
            'product_state'  => 'active',
            'travelling.limit'  => 'yes',
            'travelling.value'  => 20,
            'travelling.type'  => 'minutes',
            'product_url' => 'https://elitebookingsystem.com',
            'addon' => '0',
            'required' => '0',
            'variations' => json_encode([
                [
                    'id'=> 2,
                    'name' => 'new variation.name',
                    'stock_from_parent' => 1,
                    'is_default' => '0',
                ],
            ]),
        ]);

        $this->assertNull(session()->get('errors'));
        $response->assertStatus(302);
        $response->assertRedirect('/product/1');

        $this->assertEquals('New Product', $this->product->name);

        $updatedProduct = Product::findOrFail(2);
        $this->assertEquals('new variation.name', $updatedProduct->name);

        $this->assertEquals(1, $this->product->variations()->first()->pivot->count());
        $this->assertEquals('1', $this->product->variations()->first()->pivot->first()->stock_from_parent);
        $this->assertEquals(1, $this->product->variations()->first()->pivot->first()->parent_product_id);
        $this->assertEquals(2, $this->product->variations()->first()->pivot->first()->product_id);
    }

    public function test_product_variations_can_be_added_on_save()
    {
        $this->test_product_variation_can_be_created();

        $product3 = Product::create([
            'name' => 'DJ frankenstien',
            'description' => 'a little funk',
            'state' => 'active',
            'sku' => 'what does sku mean?',
            'owner_type' => Business::first()->getMorphClass(),
            'owner_id' => Business::first()->id,
        ]);

        // Post an update existing variation / edit product screen
        $response = $this->post(route('frontend.user.product.edit', $this->product->id), [
            'product_name'  => 'Homer simpson',
            'product_description'  => 'Main simpsons character',
            'delivery_methods'  => ['delivered'],
            'price_type'  => 'fixed',
            'product_state'  => 'active',
            'travelling.limit'  => 'yes',
            'travelling.value'  => 20,
            'travelling.type'  => 'minutes',
            'product_url' => 'https://elitebookingsystem.com',
            'variations' => json_encode([
                [
                    'id'=> 2,
                    'name' => 'new variation.name',
                    'stock_from_parent' => 1,
                    'is_default' => '0',
                ], [
                    'id'=> 3,
                    'name' => 'new new vibration',
                    'stock_from_parent' => 0,
                    'is_default' => '0',
                ],
            ]),
            'addon' => '0',
            'required' => '0',
        ]);

        $this->assertNull(session()->get('errors'));
        $response->assertStatus(302);
        $response->assertRedirect('/product/1');

        $updatedProduct2 = Product::findOrFail(2);
        $this->assertEquals('new variation.name', $updatedProduct2->name);

        $updatedProduct3 = Product::findOrFail($product3->id);
        $this->assertEquals('new new vibration', $updatedProduct3->name);

        $this->assertEquals(2, $this->product->variations()->count());

        $pivot = $this->product->variations()->first()->pivot;

        $this->assertEquals('1', $pivot->where('product_id', 2)->first()->stock_from_parent);
        $this->assertEquals(1, $pivot->where('product_id', 2)->first()->parent_product_id);
        $this->assertEquals(2, $pivot->where('product_id', 2)->first()->product_id);

        $this->assertEquals('0', $pivot->where('product_id', 3)->first()->stock_from_parent);
        $this->assertEquals(1, $pivot->where('product_id', 3)->first()->parent_product_id);
        $this->assertEquals(3, $pivot->where('product_id', 3)->first()->product_id);
    }

    public function test_product_variations_can_be_removed_on_save()
    {
        $this->test_product_variations_can_be_added_on_save();

        // Post an update existing variation / edit product screen
        $response = $this->post(route('frontend.user.product.edit', $this->product->id), [
            'product_name'  => 'Homer simpson',
            'product_description'  => 'Main simpsons character',
            'delivery_methods'  => ['delivered'],
            'price_type'  => 'fixed',
            'product_state'  => 'active',
            'travelling.limit'  => 'yes',
            'travelling.value'  => 20,
            'travelling.type'  => 'minutes',
            'product_url' => 'https://elitebookingsystem.com',
            'addon' => '0',
            'required' => '0',
            'variations' => json_encode([
                [
                    'id'=> 3,
                    'name' => 'Sneaky update?',
                    'stock_from_parent' => 0,
                    'is_default' => '0',
                ],
            ]),
        ]);

        $this->assertNull(session()->get('errors'));
        $response->assertStatus(302);
        $response->assertRedirect('/product/1');

        $this->assertEquals(1, $this->product->variations()->count());

        $pivot = $this->product->variations()->first()->pivot;

        $updatedProduct3 = Product::findOrFail(3);
        $this->assertEquals('Sneaky update?', $updatedProduct3->name);

        $this->assertEquals('0', $pivot->where('product_id', 3)->first()->stock_from_parent);
        $this->assertEquals(1, $pivot->where('product_id', 3)->first()->parent_product_id);
        $this->assertEquals(3, $pivot->where('product_id', 3)->first()->product_id);
    }

    public function test_product_variation_can_be_created()
    {

        // Given I have a dummy product
        $this->createTestProduct();
        $this->product = Product::first();

        // With  a variation
        $response = $this->post(route('frontend.user.product.variation.create', $this->product->id), []);

        //The new product id is returned in json and stock_from_parent is 0
        $response->assertStatus(200)->assertJsonFragment(['id' => 2, 'name' => 'New Product 2', 'stock_from_parent' => 0]);
    }
}

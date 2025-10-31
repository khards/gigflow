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
class ProductControllerTest extends TestCase
{
    /**
     * @var User
     */
    private $user;

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
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->business->users()->attach($this->user->id);
    }

    /**
     * Create a standard test product.
     *
     * @return \Response
     */
    protected function createTestProduct()
    {
        $response = $this->post(route('frontend.user.product.create', $this->business->id));
        $response->assertSessionHas('flash_success', __('Product successfully created.'));

        $this->assertEquals(config('app.url').'/product/1', $response->baseResponse->headers->get('location'));

        return $response;
    }

    /**
     * Test product manager can create a product from product data.
     */
    public function test_product_can_be_viewed_in_edit_screen()
    {
        // Create a product
        $response = $this->createTestProduct();
        preg_match("/\/(\d+)$/", $response->baseResponse->headers->get('location'), $matches);
        $productId = $matches[1];

        // Get / edit product screen
        $response = $this->get(route('frontend.user.product.edit', $productId));

        // Verify default attributes.
        $response->assertSee('__INITIAL_PRODUCT__');
        $response->assertSee('New Product');
        $response->assertSee('Product description');
        $response->assertSee('draft');
    }

    public function test_product_can_be_updated()
    {
        // Given I have a dummy product
        $response = $this->createTestProduct();
        preg_match("/\/(\d+)$/", $response->baseResponse->headers->get('location'), $matches);
        $productId = $matches[1];

        $image = UploadedFile::fake()->image('product-image-22342.jpg');

        // Post an update / edit product screen
        $response = $this->post(route('frontend.user.product.edit', $productId), [
            'product_name'  => 'Homer simpson',
            'product_description'  => 'Main simpsons character',
            'delivery_methods'  => ['delivered'],
            'price_type'  => 'fixed',
            'product_state'  => 'active',
            'travelling.limit'  => 'yes',
            'travelling.value'  => 20,
            'travelling.type'  => 'minutes',
            'product_image' => $image,
            'product_url' => 'https://elitebookingsystem.com',
            'addon' => '0',
            'required' => '0',
        ]);

        $this->assertNull(session()->get('errors'));
        $response->assertStatus(302);
        $response->assertRedirect('/product/1');

        // Get / edit product screen
        $response = $this->get(route('frontend.user.product.edit', $productId));

        // Verify update.
        $response->assertSee('__INITIAL_PRODUCT__');
        $response->assertSee('Homer simpson');
        $this->assertStringContainsString('"description\":\"Main simpsons character\"', $response->getContent());
        $this->assertStringContainsString('elitebookingsystem.com', $response->getContent());
    }
}

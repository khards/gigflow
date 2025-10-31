<?php

namespace Tests\Feature\Booking\Frontend\User;

use App\Booking\Business;
use App\Booking\Product;
use App\Domains\Auth\Models\User;
use Konekt\Address\Models\Address;
use Konekt\Address\Models\AddressType;
use Konekt\Address\Models\Country;
use Tests\TestCase;

class SchedulesTest extends TestCase
{
    /**
     * Test can create product.
     *
     * ProductController::class, 'create'
     */
    public function testCreateSchedule()
    {
        $user = User::factory()->create(['password' => '1234']);
        $business = $this->getBusiness();
        $route = route('frontend.user.product.create', $business->id);

        // Try to access a business we don't belong to.
        $response = $this->actingAs($user)->post($route);

        //Check we are unauthorized.
        $response->assertStatus(302);
        $this->assertEquals('This action is unauthorized.', $response->exception->getMessage());

        //Attach user to the business
        $user->businesses()->attach($business);

        // Try access the business we belong to.
        $response = $this->actingAs($user)->post($route);

        //Test we are redirect to edit product
        $response->assertStatus(302);
        $response->assertRedirect('/product/1');
    }

    /**
     * Test can view products.
     */
    public function testViewSchedules()
    {
        $user = User::factory()->create(['password' => '1234']);
        $business = $this->getBusiness();
        $routeView = route('frontend.user.schedules.view', $business->id);
        $routeCreate = route('frontend.user.schedule.create', $business->id);

        // Try to access a business we don't belong to.
        $response = $this->actingAs($user)->get($routeView);

        //Check we are unauthorized.
        $response->assertStatus(302);
        $this->assertEquals('This action is unauthorized.', $response->exception->getMessage());

        //Attach user to the business
        $user->businesses()->attach($business);

        //Create a product
        $response = $this->actingAs($user)->post($routeCreate);
        $response->assertStatus(302);
        $response->assertRedirect('/schedule/1');

        // Try access the business we belong to.
        $response = $this->actingAs($user)->get($routeView);
        $response->assertStatus(200);
//        $response->assertSee("Kugn foo rich dad poor dad - Products");
//        $response->assertSee("New Product");
    }

    /**
     * Test can update schedule.
     */
    public function testUpdateSchedule()
    {
        $user = User::factory()->create(['password' => '1234']);
        $business = $this->getBusiness();
        $product1 = factory(Product::class, [
            'owner_id' => $business->id,
            'owner_type' => Business::class,
        ])->create();
        $product2 = factory(Product::class, [
            'owner_id' => $business->id,
            'owner_type' => Business::class,
        ])->create();

        //Attach user to the business
        $user->businesses()->attach($business);

        $routeEditGet = route('frontend.user.product.edit', $product1->id);
        $routeUpdatePost = route('frontend.user.product.update', $product1->id);

        //Test that we can edit the schedule
        $response = $this->actingAs($user)->get($routeEditGet);

        //Check we are unauthorized.
        $response->assertStatus(200);

        //Update the schedule
        $response = $this->actingAs($user)->post($routeUpdatePost, [
            'name'  => 'The Moon Ring',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/product/1');
    }

    /**
     * Test can delete product.
     */
    public function testDeleteSchedule()
    {
        $user = User::factory()->create(['password' => '1234']);
        $business = $this->getBusiness();
        $routeView = route('frontend.user.products.view', $business->id);
        $routeCreate = route('frontend.user.product.create', $business->id);

        // Try to access a business we don't belong to.
        $response = $this->actingAs($user)->get($routeView);

        //Check we are unauthorized.
        $response->assertStatus(302);
        $this->assertEquals('This action is unauthorized.', $response->exception->getMessage());

        //Attach user to the business
        $user->businesses()->attach($business);

        //Create a product
        $response = $this->actingAs($user)->post($routeCreate);
        $response->assertStatus(302);
        $response->assertRedirect('/product/1');

        // Delete the product
        $routeDelete = route('frontend.user.product.delete', 1);
        $response = $this->actingAs($user)->delete($routeDelete);
        $response->assertStatus(302);

        // Try access the business we belong to.
        $response = $this->actingAs($user)->get($routeView);
        $response->assertStatus(200);
        $response->assertDontSee('New Product');
    }
}

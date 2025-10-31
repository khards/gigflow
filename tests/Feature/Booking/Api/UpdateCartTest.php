<?php

namespace Tests\Feature\Booking\Api;

use App\Booking\Availability\Checker\Checker;
use App\Booking\Contracts\ProductManager;
use App\Booking\Product;
use App\Booking\Services\ScheduleManager;
use App\Domains\Auth\Models\User;
use App\Domains\Form\Contracts\FormManager;
use Illuminate\Support\Facades\Cache;
use Pnlinh\GoogleDistance\Contracts\GoogleDistanceContract;
use Tests\Feature\Booking\Availability\Mock\MockGoogleDistance;
use Tests\Feature\Booking\BookingTestDataGenerator;
use Tests\Feature\Booking\ProductGenerator;

class UpdateCartTest extends BookingTestDataGenerator
{
    use ProductGenerator;

    protected string $lastReference;

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var ScheduleManager
     */
    protected $scheduleManager;

    /**
     * @var Checker
     */
    private $checker;

    /**
     * Setup a product manager instance.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->productManager = app()->make(ProductManager::class);

        $this->business = $this->getBusiness();
        $this->user = User::factory()->create();
        $this->business->users()->attach($this->user->id);
        $this->scheduleManager = $this->app->make(ScheduleManager::class);
        $this->checker = new Checker();
        $this->product = $this->createDefaultServiceProduct($this->business);
        $this->importUserSchedule();
        $this->createAndAttachProductAndBusinessSchedule($this->product, $this->business);

        // As API user
        $this->actingAs($this->user, 'api');

        //With the test google distance calculator
        $this->app->bind(GoogleDistanceContract::class, MockGoogleDistance::class);
    }

    public function test_add_single___dodgy_product_id__to_cart()
    {
        $request = [
            'businessId' => $this->business->id,
            'products' => [
                ['id' => 777, 'quantity' => 1],
            ],
            'start' => '2021-09-09 19:00:00',
            'end' =>   '2021-09-10 00:00:00',
            'location' => 'CT17 0bs',
        ];

        $testResponse = [
            'status' => 'error',
            'errors' => [
                0 => 'Invalid product id',
            ],
            'number_items_in_cart' => '0',
            'number_lines_in_cart' => '0',
            'reference' => '1',
            'price' => [
                'adjustments' => (string) 0,
                'total_price' => ((string) ((0.00 * 2) + 0.00)),
                'dispatch_price' => (string) (0.0 * 2),
                'deposit' => (string) 0.00,
            ],
        ];

        $response = $this->patch(route('api.cart.update', $request), ['accept' => 'application/json']);
        $response->assertStatus(302);
        $this->assertEquals('The given data was invalid.', $response->exception->getMessage());
        $response->assertSessionHasErrors(['products.0.id' => 'The selected products.0.id is invalid.']);
    }

    /**
     * Test adding available service, with unavailable product..
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function test_add_multiple___service_with_unavailable_product__to_cart()
    {
        $this->setupScheduledProduct();

        // Service product.
        // £123.27 between 2021-09-09 0:00:00 and 2021-09-10 00:00:00'
        //
        $this->product->update([
            'travelling_value' => 150, //150 miles.  * 1609
        ]);

        //Product fixed 200.00 + 12.00 per item
        $productProduct = $this->createDefaultServiceProduct($this->business);
        $productProduct->type = 'product';
        $productProduct->available_quantity = 0;
        $productProduct->price_fixed_price = 200 * 100;
        $productProduct->price_type = 'fixed';
        $productProduct->availability_type = 'available';
        $productProduct->delivery_method = Product::DELIVERY_METHODS['shipped'];
        $productProduct->settings->set('delivery.shipped.price', 12.00);
        $productProduct->settings->set('delivery.shipped.per', 'item');
        $productProduct->save();

        $request = [
            'businessId' => $this->business->id,
            'products' => [

                //Product
                ['id' => $productProduct->id, 'quantity' => 2],

                //Service
                ['id' => $this->product->id, 'quantity' => 1],
            ],
            'start' => '2021-09-09 19:00:00',
            'end' =>   '2021-09-10 00:00:00',
            'location' => 'CT17 0bs',
        ];

        $testResponse = [
            'status' => 'error',
            'errors' => [
                2 => 'Product is unavailable',
            ],
            'number_items_in_cart' => '1',
            'number_lines_in_cart' => '1',
            'reference' => '1',
            'price' => [
                'adjustments' => (string) 0,
                'total_price' => (string) round(123.27 + 0.00),
                'dispatch_price' => (string) (0),
                'deposit' => (string) 20.00,
            ],
        ];

        /**
         * We are checking here that when we book on 2021-09-09 19:00:00 to 2021-09-10 00:00:00, staff are available and
         * the correct price for the booking is returned.
         */
        $response = $this->patch(route('api.cart.update', $request), ['accept' => 'application/json']);
        $response->assertStatus(200)->assertJsonFragment($testResponse);
    }

    //product id=2, travelling_value=12.65, need >117.97
    /**
     * Add a service + product to the cart (both available).
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function test_add_multiple___service_with_product__to_cart()
    {
        $this->setupScheduledProduct();

        // Service product.
        // £123.27 between 2021-09-09 0:00:00 and 2021-09-10 00:00:00'
        //
        $this->product->update([
            'travelling_value' => 150, //150 miles.  * 1609
        ]);

        //Product fixed 200.00 + 12.00 per item
        $productProduct = $this->createDefaultServiceProduct($this->business);
        $productProduct->type = 'product';
        $productProduct->available_quantity = 5;
        $productProduct->price_fixed_price = 200 * 100;
        $productProduct->price_type = 'fixed';
        $productProduct->availability_type = 'available';
        $productProduct->delivery_method = Product::DELIVERY_METHODS['shipped'];
        $productProduct->settings->set('delivery.shipped.price', 12.00);
        $productProduct->settings->set('delivery.shipped.per', 'item');
        $productProduct->save();

        $request = [
            'businessId' => $this->business->id,
            'products' => [

                //Product
                ['id' => $productProduct->id, 'quantity' => 2],

                //Service
                ['id' => $this->product->id, 'quantity' => 1],
            ],
            'start' => '2021-09-09 19:00:00',
            'end' =>   '2021-09-10 00:00:00',
            'location' => 'LO00CA',
        ];

        $testResponse = [
            'status' => 'ok',
//            'number_items_in_cart' => '2',
            'number_lines_in_cart' => '3',
            'reference' => '1',
            'price' => [
                'adjustments' => (string) 0,
                'total_price' => ((string) round(((200.00 * 2) + 24.00) + (123.27 + 0.00))), //
                'dispatch_price' => (string) ((12.0 * 2) /* Shipping cost*/ +0.00 /* Delivery cost*/),
                'deposit' => (string) 59.00,
            ],
        ];

        /**
         * We are checking here that when we book on 2021-09-09 19:00:00 to 2021-09-10 00:00:00, staff are available and
         * the correct price for the booking is returned.
         */
        $response = $this->patch(route('api.cart.update', $request), ['accept' => 'application/json']);
        $response->assertStatus(200)->assertJsonFragment($testResponse);

        //We are calling this from out place order test. I need to store the reference id
        $this->lastReference = json_decode($response->getContent())->data->reference;
    }

    /**
     * Add 2 x 1 shipped item to the cart with shipping price per item.
     */
    public function test_add_single___available_product___to_cart()
    {
        $this->product->type = 'product';
        $this->product->available_quantity = 5;
        $this->product->price_fixed_price = 200 * 100;
        $this->product->price_type = 'fixed';
        $this->product->availability_type = 'available';
        $this->product->delivery_method = Product::DELIVERY_METHODS['shipped'];
        $this->product->settings->set('delivery.shipped.price', 12.00);
        $this->product->settings->set('delivery.shipped.per', 'item');
        $this->product->save();

        $request = [
            'businessId' => $this->business->id,
            'products' => [
                ['id' => $this->product->id, 'quantity' => 2],
            ],
            'start' => '2021-09-09 19:00:00',
            'end' =>   '2021-09-10 00:00:00',
            'location' => 'CT17 0bs',
        ];

        $testResponse = [
            'status' => 'ok',
            'number_items_in_cart' => '1',
            'number_lines_in_cart' => '2',
            'reference' => '1',
            'price' => [
                'adjustments' => (string) 0,
                'total_price' => ((string) ((200.00 * 2) + (12.00 * 2))),
                'dispatch_price' => (string) (12.0 * 2),
                'deposit' => (string) 59.00,
            ],
        ];

        /**
         * We are checking here that when we book on 2021-09-09 19:00:00 to 2021-09-10 00:00:00, staff are available and
         * the correct price for the booking is returned.
         */
        $response = $this->patch(route('api.cart.update', $request), ['accept' => 'application/json']);
        $response->assertStatus(200)->assertJsonFragment($testResponse);

        // Verify stock has been deducted.? ___No___, that's is after payment/checkout
    }

    public function test_add_single___unavailable_product___to_cart()
    {
        $this->product->type = 'product';
        $this->product->available_quantity = 1;
        $this->product->price_fixed_price = 200 * 100;
        $this->product->price_type = 'fixed';
        $this->product->availability_type = 'available';
        $this->product->delivery_method = Product::DELIVERY_METHODS['shipped'];
        $this->product->settings->set('delivery.shipped.price', 12.00);
        $this->product->settings->set('delivery.shipped.per', 'item');
        $this->product->save();

        $request = [
            'businessId' => $this->business->id,
            'products' => [
                ['id' => $this->product->id, 'quantity' => 2],
            ],
            'start' => '2021-09-09 19:00:00',
            'end' =>   '2021-09-10 00:00:00',
            'location' => 'CT17 0bs',
        ];

        $testResponse = [
            'status' => 'error',
            'errors' => [
                1 => 'Product is unavailable',
            ],
            'number_items_in_cart' => '0',
            'number_lines_in_cart' => '0',
            'reference' => '1',
            'price' => [
                'adjustments' => (string) 0,
                'total_price' => ((string) ((0.00 * 2) + 0.00)),
                'dispatch_price' => (string) (0.0 * 2),
                'deposit' => (string) 0.00,
            ],
        ];

        /**
         * We are checking here that when we book on 2021-09-09 19:00:00 to 2021-09-10 00:00:00, staff are available and
         * the correct price for the booking is returned.
         */
        $response = $this->patch(route('api.cart.update', $request), ['accept' => 'application/json']);
        $response->assertStatus(200)->assertJsonFragment($testResponse);
    }

    public function test_add_single___unavailable_service__to_cart()
    {
        $this->setupScheduledProduct();

        $this->product->update([
            'travelling_value' => 100, //100 miles.
        ]);

        $request = [
            'businessId' => $this->business->id,
            'products' => [
                ['id' => $this->product->id, 'quantity' => 1],
            ],
            'start' => '2021-09-09 19:00:00',
            'end' =>   '2021-09-10 00:00:00',
            'location' => 'CT17 0bs',
        ];

        $testResponse = [
            'status' => 'error',
            'errors' => [
                1 => 'Product is unavailable',
            ],
            'number_items_in_cart' => '0',
            'reference' => '1',
            'price' => [
                'adjustments' => '0',
                'total_price' => '0',
                'dispatch_price' => '0',
                'deposit' => '0',
            ],
        ];

        /**
         * We are checking here that when we book on 2021-09-09 19:00:00 to 2021-09-10 00:00:00, staff are available and
         * the correct price for the booking is returned.
         */
        $response = $this->patch(route('api.cart.update', $request), ['accept' => 'application/json']);

        $response->assertStatus(200)->assertJsonFragment($testResponse);
    }

    public function test_add_single_scheduled_service_with_staff_to_cart()
    {
        $this->setupScheduledProduct();

        $this->product->update([
            'travelling_value' => 150, //150 miles.  * 1609
        ]);

        $request = [
            'businessId' => $this->business->id,
            'products' => [
                ['id' => $this->product->id, 'quantity' => 1],
            ],
            'start' => '2021-09-09 19:00:00',
            'end' =>   '2021-09-10 00:00:00',
            'location' => 'CT17 0bs',
        ];

        $testResponse = [
            'status' => 'ok',
            'number_items_in_cart' => '1',
            'reference' => '1',
            'price' => [
                'adjustments' => (string) 0,
                'total_price' => ((string) round(123.27 + 0.00)),
                'dispatch_price' => (string) 0,
                'deposit' => (string) 20,
            ],
        ];

        /**
         * We are checking here that when we book on 2021-09-09 19:00:00 to 2021-09-10 00:00:00, staff are available and
         * the correct price for the booking is returned.
         */
        $response = $this->patch(route('api.cart.update', $request), ['accept' => 'application/json']);
        $response->assertStatus(200)->assertJsonFragment($testResponse);
    }

    /**
     * Update cart with form tests.
     *
     * This test sumulates the cart update with a form submission.
     * 1. Setup test data
     * 2. Submit cart with a form and a response that 'skips' to another form.
     * 3. Checks that the correct skipped form is returned.
     * 4. Checks the cache to ensure that the submitted form was cached for create order.
     * 5. Sumbit returned form, with back button -> Check pervious cached page was returned, check subbmission is cached.
     *
     * Submitting form data with the cart update, then check that submitted data is stored in cache.
     *
     * @return void
     */
    public function test_cache_submit_form_to_cart()
    {
        // Setup test data:
        //  Create second form that has skip logic
        //  Create first form that is attached to the product and has skip logic.
        {
            // Given that I set up a product
            $this->setupScheduledProduct();
            $this->product->update(['travelling_value' => 150]);

            // Given there is a 2nd 'skipped to' form
            $formData = [
                'name' => 'My test form',
                'data' => [
                    'element_name' => 'input',
                    'event_type' => [
                        'wedding',
                        'other'
                    ]
                ],
                'action' => [],// Second form has no logic.
            ];

            // Given the form is created
            $formManager = app()->make(FormManager::class);
            $secondFormNoLogic = $formManager->create($this->business->id, $formData);

            // Given I create the first form  to submit
            $firstFormWithLogicData = $this->getRawTestFormData();
            $firstFormWithLogicData['required'] = true;
            $formData['data'] = $firstFormWithLogicData;

            $formData['action'] = [
                'required' => '1',
                'type' => '',
                'logic_question_name' => 'event-type',
                'logic_response' => 'wedding',
                'logic' => (object) [
                    'wedding' => $secondFormNoLogic->id,
                    'other' => '',
                ],
            ];
            $firstForm = $formManager->create($this->business->id, $formData);

            // ** Test attaching form to product!

            // *** Attach the current form to the product being ordered.
            $this->product->update(['form_id' => $firstForm->id]);

            // Select a logic 'skip' response for the first form submission.
            $firstFormWithLogicData[3]->userData = ['wedding'];
        }

        // 2. Given I have a request with the submission for the "first form" containing the skip answer
        {
            $request = [
                'businessId' => $this->business->id,
                'products' => [
                    ['id' => $this->product->id, 'quantity' => 1],
                ],
                'start' => '2021-09-09 19:00:00',
                'end' =>   '2021-09-10 00:00:00',
                'location' => 'CT17 0bs',

                // Send in the filled out form.
                'formData' => json_encode($firstFormWithLogicData),
                'currentFormId' => $firstForm->id,
            ];

            // Expected test response is the second "skipped to" form.
            $testResponse = [
                'status' => 'ok',
                'number_items_in_cart' => '1',
                'reference' => '1',
                'price' => [
//                    'adjustments' => (string) 0,
//                    'total_price' => ((string) (123.27 + 14.00)),
//                    'dispatch_price' => (string) 14,
//                    'deposit' => (string) 21,
                    'adjustments' => (string) 0,
                    'total_price' => ((string) round(123.27 + 0)),
                    'dispatch_price' => (string) 0,
                    'deposit' => (string) 20,
                ],
                'formId' => $secondFormNoLogic->id,

                // Expect skipped to form to be returned.
                'formData' => json_encode((object)['element_name' => 'input', 'event_type' => ['wedding', 'other']]),
            ];

            // Then the second form (skipped to) is returned.
            $response = $this->patch(route('api.cart.update', $request), ['accept' => 'application/json']);

            // *3. Checks that the correct skipped form is returned.
            $response->assertStatus(200)->assertJsonFragment($testResponse);

            $responseData = json_decode($response->baseResponse->getContent());
        }

        // *4. Check cache.
        {
            // Then data was stored in the cache, so that we can store it to DB when creating order.
            $cacheKey = $responseData->data->sessionId.'_form';
            $storedResponses = Cache::get($cacheKey);

            $this->assertEquals(
                json_decode(json_encode($firstFormWithLogicData, true)),
                json_decode(json_encode($storedResponses[$firstForm->id], true))
            );
        }

        // *5. Press back button. previous fist form with response will be returned.
        {
            $backRequest = $request;

            // need to pass through session to get cached response!!
            $backRequest['sessionId'] = $responseData->data->sessionId;

            $backRequest['navAction'] = 'backward';
            $backRequest['currentFormId'] = $testResponse['formId'];
            $backRequest['formData'] = $testResponse['formData'];

            // Then the first form is returned with it's response.
            $response = $this->patch(route('api.cart.update', $backRequest), ['accept' => 'application/json']);

            // Expected test response is the originally submittedpage with it's response.
            $testResponse = [
                'status' => 'ok',
                'number_items_in_cart' => '1',
                'reference' => '1',
                'formId' => $firstForm->id,
                'formData' => json_encode($firstFormWithLogicData),
            ];

            $response->assertStatus(200)->assertJsonFragment($testResponse);
        }
    }

    // ****************************
    //  Private helpers.
    // ****************************
    private function setupScheduledProduct()
    {
        // Scheduled product price
        $this->product->update(['price_type' => 'scheduled']);

        // With schedule 1 2021-09-09 0:00:00 - 2021-09-10 00:00:00 = £123.27 per booking
        $schedule = $this->scheduleManager->create($this->business, [
            'summary' => 'Scheduled price 1',
            'start_datetime' => '2021-09-09 0:00:00',
            'end_datetime' => '2021-09-10 00:00:00',
        ]);
        $this->product->schedules()->save($schedule, [
            'key' => 'booking',
            'value' => '12327',
        ]);

        // With schedule 1 2021-10-10 00:00:00 - 2021-10-17 12:00:00 = £153.35 per booking
        $schedule2 = $this->scheduleManager->create($this->business, [
            'summary' => 'Scheduled price 2',
            'start_datetime' => '2021-10-10 00:00:00',
            'end_datetime' => '2021-10-17 12:00:00',
        ]);
        $this->product->schedules()->save($schedule2, [
            'key' => 'booking',
            'value' => '15335',
        ]);
    }

    private function getRawTestFormData()
    {
        return [
            0 => (object) [
                'type' => 'header',
                'subtype' => 'h1',
                'label' => 'Header',
                'access' => false,
            ],
            1 => (object) [
                'type' => 'text',
                'required' => false,
                'label' => 'First Name',
                'className' => 'form-control',
                'name' => 'firstname',
                'access' => false,
                'subtype' => 'text',
            ],
            2 => (object) [
                'type' => 'text',
                'required' => false,
                'label' => 'Last Name',
                'className' => 'form-control',
                'name' => 'lastname',
                'access' => false,
                'subtype' => 'text',
            ],
            3 => (object) [
                'type' => 'autocomplete',
                'required' => false,
                'label' => 'Event Type',
                'className' => 'form-control',
                'name' => 'event-type',
                'access' => false,
                'requireValidOption' => false,
                'values' => [
                    0 => (object) [
                        'label' => 'Wedding',
                        'value' => 'wedding',
                        'selected' => true,
                    ],
                    1 => (object) [
                        'label' => 'Birthday',
                        'value' => 'birthday',
                        'selected' => false,
                    ],
                    2 => (object) [
                        'label' => 'Other',
                        'value' => 'other',
                        'selected' => false,
                    ],
                ],
            ],
            4 => (object) [
                'type' => 'radio-group',
                'required' => false,
                'label' => 'Radio Group',
                'inline' => false,
                'name' => 'radio-group-1629966624736',
                'access' => false,
                'other' => false,
                'values' => [
                    0 => (object) [
                    'label' => 'Option 1',
                    'value' => 'option-1',
                    'selected' => false,
                    ],
                    1 => (object) [
                    'label' => 'Option 2',
                    'value' => 'option-2',
                    'selected' => false,
                    ],
                    2 => (object) [
                    'label' => 'Option 3',
                    'value' => 'option-3',
                    'selected' => false,
                    ],
                ],
            ],
            5 => (object) [
                'type' => 'select',
                'required' => false,
                'label' => 'Select',
                'className' => 'form-control',
                'name' => 'select-1629966628848',
                'access' => false,
                'multiple' => false,
                'values' => [
                    0 => (object) [
                        'label' => 'Option 1',
                        'value' => 'option-1',
                        'selected' => true,
                    ],
                    1 => (object) [
                        'label' => 'Option 2',
                        'value' => 'option-2',
                        'selected' => false,
                    ],
                    2 => (object) [
                        'label' => 'Option 3',
                        'value' => 'option-3',
                        'selected' => false,
                    ],
                ],
            ],
        ];
    }
}

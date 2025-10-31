<?php

namespace Tests\Feature\Booking\Api;

use App\Domains\Form\Models\Form;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;

class CreateOrderTest extends UpdateCartTest
{
    /**
     * Place a new order and create customer in process.
     *
     * @throws BindingResolutionException
     */
    public function test_creates_order_checkout()
    {
        // Form session ID
        $sessionId = 'TEST12312';

        //Add items to the cart
        parent::test_add_multiple___service_with_product__to_cart();

        // Add required form data to cached forms.
        $this->requiredFormTestDataToCache($sessionId);

        $request = [
            'sessionId' => $sessionId,
            'businessId' => $this->business->id,
            'reference' => $this->lastReference,
            'start' => '2021-09-09 19:00:00',
            'end' =>   '2021-09-10 00:00:00',
            'location' => 'CT17 0bs',
            'products' => [
                ['id' => $this->product->id, 'quantity' => 1],
            ],
        ];

        // Post "place order" to the Cart API
        $response = $this->post(route('api.checkout.order'), $request, ['accept' => 'application/json']);
        $response->assertSuccessful();
    }

    /**
     * Required form data for testing.
     *
     * @param string $sessionId
     * @return void
     */
    private function requiredFormTestDataToCache(string $sessionId): void
    {
        $data = [
            (object) [
                'type' => 'header',
                'subtype' => 'h1',
                'label' => 'Your details',
                'access' => false,
            ],
            (object) [
                'type' => 'text',
                'required' => true,
                'label' => 'First Name',
                'className' => 'form-control',
                'name' => 'first-name',
                'access' => false,
                'subtype' => 'text',
                'userData' => [
                    'Fred',
                ],
            ],
            (object) [
                'type' => 'text',
                'required' => true,
                'label' => 'Last Name',
                'className' => 'form-control',
                'name' => 'last-name',
                'access' => false,
                'subtype' => 'text',
                'userData' => [
                    'Flintstone',
                ],
            ],
            (object) [
                'type' => 'text',
                'subtype' => 'email',
                'required' => true,
                'label' => 'Email Address',
                'description' => 'This will be used to send your booking confirmation email and enable you to login.',
                'className' => 'form-control',
                'name' => 'email',
                'access' => false,
                'userData' => [
                    'jeff.woods.1957@hotmales.com',
                ],
            ],
            (object) [
                'type' => 'text',
                'subtype' => 'tel',
                'required' => true,
                'label' => 'Phone number',
                'description' => 'This will be used if we need to contact you about your booking',
                'className' => 'form-control',
                'name' => 'phone',
                'access' => false,
                'userData' => [
                    '07806 303755',
                ],
            ],
            (object) [
                'type' => 'header',
                'subtype' => 'h2',
                'label' => 'Login details',
                'access' => false,
            ],
            (object) [
                'type' => 'text',
                'subtype' => 'password',
                'required' => true,
                'label' => 'New password',
                'description' => 'This will enable you to login.',
                'className' => 'form-control',
                'name' => 'password',
                'access' => false,
                'userData' => [
                    'P3n1s',
                ],
            ],
            (object) [
                'type' => 'header',
                'subtype' => 'h2',
                'label' => 'Your address',
                'access' => false,
            ],
            (object) [
                'type' => 'select',
                'required' => true,
                'label' => 'Country',
                'className' => 'form-control',
                'name' => 'country',
                'access' => false,
                'multiple' => false,
                'values' => [
                    (object) [
                        'label' => 'United Kingdom',
                        'value' => 'GB',
                        'selected' => true,
                    ],
                    (object) [
                        'label' => 'United Kingdom',
                        'value' => 'GB',
                        'selected' => true,
                    ],
                ],
                'userData' => [
                    'GB',
                ],
            ],
            (object) [
                'type' => 'text',
                'required' => true,
                'label' => 'Address (House number and street)',
                'className' => 'form-control',
                'name' => 'address-street',
                'access' => false,
                'subtype' => 'text',
                'userData' => [
                    '14 Nacatia road, nuttyland',
                ],
            ],
            (object) [
                'type' => 'text',
                'required' => true,
                'label' => 'Address (Locality, town)',
                'className' => 'form-control',
                'name' => 'address-town',
                'access' => false,
                'subtype' => 'text',
                'userData' => [
                    'Bannana town',
                ],
            ],
            (object) [
                'type' => 'text',
                'required' => true,
                'label' => 'Post Code',
                'description' => 'Your postcode',
                'className' => 'form-control',
                'name' => 'postcode',
                'access' => false,
                'subtype' => 'text',
                'maxlength' => 9,
                'userData' => [
                    '8008 1355',
                ],
            ],
        ];

        $form = Form::create([
            'name' => 'booking_user',
            'data' => json_encode($data),
            'action' => ['logic_question_name' => 'event-type'],
            'settings' => json_encode(['setting' => 'ho foo you!']),
            'required' => true,
            'shared' => true,
        ]);

        $forms = Cache::get($sessionId.'_form');
        $forms[$form->id] = $data;
        Cache::put($sessionId.'_form', $forms);
    }
}

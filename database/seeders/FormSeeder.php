<?php

namespace Database\Seeders;

use App\Booking\Business;
use App\Domains\Form\Models\Form;
use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    /**
     * Run the shared form seed.
     *
     * @return void
     */
    public function run()
    {
        $this->requiredFormData();
//        $requiredForm = config('checkout');
//        foreach ($requiredForm as $formItems) {
//            $formName = $formItems['name'];
//            $formItems = $formItems['items'];
//            // $key => $formkey
//            //$formId = Form::whereIn('id', array_keys($userSubmittedForms))->where('name', $requiredFormName)->pluck('id')->first();
//        }
    }

    /**
     * This all really need to go intop the config and the parser use this.
     * The "seeder" will also use it to populate a new for for each business.
     */

    private function requiredFormData(): void
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
                    '',
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
                    '',
                ],
            ],
            (object) [
                'type' => 'text',
                'subtype' => 'email',
                'required' => true,
                'label' => 'Email',
                'description' => 'We will send your booking confirmation email to this address',
                'className' => 'form-control',
                'name' => 'email',
                'access' => false,
                'userData' => [
                    '',
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
                    '',
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
                    '',
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
                    '',
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
                    '',
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
                    '',
                ],
            ],
        ];

        dd("Please fix me!!");
        $business = Business::first();

        Form::create([
//            'owner_type' => '',
//            'owner_id' => null,

            'owner_type' => $business->getMorphClass(),
            'owner_id' => $business->id,

            'name' => 'booking_user',
            'data' => json_encode($data),
            'action' => '',
            'settings' => '',
            'required' => true,
            'shared' => true,
        ]);
    }
}

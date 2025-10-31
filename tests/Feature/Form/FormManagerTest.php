<?php

namespace Tests\Feature\Form;

use App\Booking\Business;
use App\Booking\Cart\CartManager;
use App\Booking\Contracts\ProductManager;
use App\Booking\Product;
use App\Domains\Form\Contracts\FormManager;
use App\Domains\Form\Contracts\FormResponseManager;
use App\Domains\Form\Models\Form;
use Tests\TestCase;

/**
 * This is for testing the Form Manager service.
 *
 * Class FormServiceTest
 */
class FormManagerTest extends TestCase
{
    /**
     * @var Business
     */
    private $business;

    /**
     * @var FormManager
     */
    private $formManager;

    /**
     * @var FormResponseManager
     */
    private $formResponseManager;

    // Given some form data
    public static $formDataSkipOnWedding = [
        'name' => 'My test formz',
        'data' => ['element_name' => 'input', 'event_type' => ['wedding', 'birthday']],
        'action' => ['submit' => 'next'],
        'settings' => [
            'foo' => 'bar',
            'skip_to_page' => [
                'source_name' => 'event_type',
                'value' => 'wedding',
                'next_page' => 'wedding details',
            ],
        ],
    ];

    /**
     * Setup a dummy business and a form manager instance.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->business = $this->getBusiness();
        $this->formManager = app()->make(FormManager::class);
        $this->formResponseManager = app()->make(FormResponseManager::class);
    }

    public function test_get_required_form_ids()
    {
        $businessId = 1;
        $testFormData = static::$formDataSkipOnWedding;
        $testFormData['required'] = true;
        $this->createTestForm($testFormData);
        $this->createTestForm($testFormData);

        $this->createTestForm(static::$formDataSkipOnWedding);

        $formIds = $this->formManager->getRequiredFormIds($businessId);

        // No required forms..
        $this->assertCount(2, $formIds);
    }

    public function test_get_product_form_ids()
    {
        $testFormData = static::$formDataSkipOnWedding;
        $testFormData['required'] = true;
        $req1 = $this->createTestForm($testFormData);
        $req2 = $this->createTestForm($testFormData);
        $nonreq1 = $this->createTestForm(static::$formDataSkipOnWedding);

        $productManager = app()->make(ProductManager::class);

        // Given some product data
        $productData = [
            'name' => 'ebay KFC powder 450g',
            'description' => 'Some good ol fried chickn powder',
            'state' => 'active',
            'sku' => 'fried-chkn-450g',
            'delivery_method' => Product::DELIVERY_METHODS['delivered'] | Product::DELIVERY_METHODS['collected'],
            'form_id' => $nonreq1->id,
        ];

        //When I create a product with associated form
        $product = $productManager->create(
            $this->business->id,
            $productData
        );

        $productData['form_id'] = $req2->id;
        $product2 = $productManager->create(
            $this->business->id,
            $productData
        );

        $productIds = [$product->id, $product2->id];

        $formIds = $this->formManager->getProductFormIds($productIds);

        // No required forms..
        $this->assertCount(2, $formIds);
    }

    /**
     * Test that all required forms for given product ids and business are returned.
     *
     * Make 4 forms
     *  Form 1, required
     *  Form 2, required
     *  Form 3, not required - associated product 1, associated product 2
     *  Form 4, not required or associated.
     *
     *  Forms 1,2,3 should be returned. This proves that the forms are in correct order and dupes filtered.
     */
    public function test_form_manager_all_required_form_ids()
    {
        $businessId = 1;

        $testFormData = static::$formDataSkipOnWedding;
        $testFormData['required'] = true;
        $nonreq1 = $this->createTestForm(static::$formDataSkipOnWedding);
        $req1 = $this->createTestForm($testFormData);
        $req2 = $this->createTestForm($testFormData);

        $productManager = app()->make(ProductManager::class);

        // Given some product data
        $productData = [
            'name' => 'ebay KFC powder 450g',
            'description' => 'Some good ol fried chickn powder',
            'state' => 'active',
            'sku' => 'fried-chkn-450g',
            'delivery_method' => Product::DELIVERY_METHODS['delivered'] | Product::DELIVERY_METHODS['collected'],
        ];

        //When I create a product with a form
        $productData['form_id'] = $nonreq1->id;
        $product1 = $productManager->create(
            $this->business->id,
            $productData
        );

        // Make 2nd product same form
        $productData['form_id'] = $nonreq1->id;
        $product2 = $productManager->create(
            $this->business->id,
            $productData
        );

        $productIds = [$product1->id, $product2->id];

        $ids = $this->formManager->getAllRequiredFormIds($businessId, $productIds);

        $expected = [$req1->id, $req2->id, $nonreq1->id];

        $this->assertEquals($expected, $ids);
    }

    // 1, No required forms, return null
    public function test_form_manager_no_form()
    {
        $productIds = [1, 2, 3, 4, 354];
        $businessId = 1;
        $cartId = 1;
        $submittedFormdata = static::$formDataSkipOnWedding;
        $visitedFromIds = [];

        $testFormData = static::$formDataSkipOnWedding;
        $testFormData['required'] = false;
        $requiredForm1 = $this->createTestForm($testFormData);
        $requiredForm2 = $this->createTestForm($testFormData);
        $nonRequiredForm1 = $this->createTestForm(static::$formDataSkipOnWedding);

        $firstFormId = $this->formManager->getNextFormId(
            $businessId,
            $cartId,
            $submittedFormdata,
            $productIds,
            $visitedFromIds,
        );

        // first required form
        $this->assertEquals(null, $firstFormId);
    }

    // 2, If $visitedFormIds is empty, return first form
    public function test_form_manager_first_form()
    {
        $productIds = [354];
        $businessId = 1;
        $cartId = 1;
        $submittedFormdata = static::$formDataSkipOnWedding;
        $visitedFromIds = [];

        $testFormData = static::$formDataSkipOnWedding;
        $testFormData['required'] = true;
        $requiredForm1 = $this->createTestForm($testFormData);
        $requiredForm2 = $this->createTestForm($testFormData);
        $nonRequiredForm1 = $this->createTestForm(static::$formDataSkipOnWedding);

        $firstFormId = $this->formManager->getNextFormId(
            $businessId,
            $cartId,
            $submittedFormdata,
            $productIds,
            $visitedFromIds,
        );

        // first required form
        $this->assertEquals($requiredForm1->id, $firstFormId);
    }

    // 7, The next form ID.
    public function test_form_manager_next_form()
    {

        // Given some test forms
        $productIds = [354];
        $businessId = 1;
        $cartId = 1;

        $testFormData = $this->getRawTestFormData();
        $testFormData['required'] = true;
        $requiredForm1 = $this->createTestForm($testFormData);
        $requiredForm2 = $this->createTestForm($testFormData);

        // When we have visited form 1
        $visitedFromIds = [$requiredForm1->id];

        $nextFormId = $this->formManager->getNextFormId(
            $businessId,
            $cartId,
            $testFormData,
            $productIds,
            $visitedFromIds,
        );

        // Then we expect form 2
        $this->assertEquals($requiredForm2->id, $nextFormId);
    }

    /**
     * Skip to a form based on the form-logic skip
     * Make 3 forms.
     * 1. Form 1 required -> Skip to form 3
     * 2. Form 2 not required and should not be seen by user.
     * 3. Form 3 should be returned due to form 1.
     */
    public function test_next_form_logic()
    {
        $businessId = 1;
        $productManager = app()->make(ProductManager::class);

        $testFormData1 = [
            'name' => 'My skipping test form',
            'required' => true,
            'data' => ['element_name' => 'input', 'shmoo' => ['too', 'you']],
            'action' => (object) [
                'required' => '0',
                'type' => 'next',
                'logic_form' => '3',
            ],
        ];

        $form1 = $this->createTestForm($testFormData1);
        $form2 = $this->createTestForm($testFormData1);
        $form3 = $this->createTestForm($testFormData1);

        // Given some test product data
        $productData = [
            'name' => 'ebay KFC powder 450g',
            'description' => 'Some good ol fried chickn powder',
            'state' => 'active',
            'sku' => 'fried-chkn-450g',
            'delivery_method' => Product::DELIVERY_METHODS['delivered'] | Product::DELIVERY_METHODS['collected'],
        ];

        $product = $productManager->create(
            $this->business->id,
            $productData
        );


        // When we have not visited a form, expect the first form to be returned.
        $visitedFromIds = [];
        $submittedFormdata = [];
        $productIds = [$product->id];
        $nextFormId = $this->formManager->getNextFormId(
            $businessId,
            $cartId = 1,
            $submittedFormdata,
            $productIds,
            $visitedFromIds,
        );

        // 1. Get form 1 - expect form 1
        $this->assertEquals($form1->id, $nextFormId);

        // 2. Submit form 1 - expect form 3
        $visitedFromIds = [$nextFormId];

        //Not sure submitting this will work?
        $submittedFormdata = $testFormData1;

        $nextFormId = $this->formManager->getNextFormId(
            $businessId,
            $cartId = 1,
            $submittedFormdata,
            $productIds,
            $visitedFromIds,
        );
        $this->assertEquals($form3->id, $nextFormId);

    }

    /**
     *  3, Skip to a form based on submitted form.
     *
     *  Make 4 forms
     *   Form 1, required
     *   Form 2, required
     *   Form 3, not required - associated product 1, SKIP to form 4
     *   Form 4, not required or associated.
     *   Form 5, not required - associated product 2
     */
    public function test_form_manager_skip_on_response()
    {
        $formData = $this->getRawTestFormData();

        $actionPageSkipTo4 = (object) [
            'required' => '0',
            'type' => 'next',// *
            'logic_question_name' => '',
            'logic_response' => '',
            'logic' => (object) [],
            'logic_form' => '4', // *
        ];

        $actionLogicSkipEventTypeWedding = (object) array(
            'type' => 'logic',
            'logic_question_name' => 'event-type',
            'logic_response' => '',
            'logic_form' => '',
            'logic' => array (
                'wedding' => '20',
                'civil ceremony' => '20',
                'birthday' => '21',
                'corporate' => '45',
                'other' => '45',
            ),
        );
        
        $businessId = 1;
        $productManager = app()->make(ProductManager::class);

        $testFormData = static::$formDataSkipOnWedding;
        $testFormData['required'] = true;
        $form1_required = $this->createTestForm($testFormData);              // 1
        $form2_required = $this->createTestForm($testFormData);              // 2

        // Setup skip logic on form 3 to skip to form 4
        $test3FormData = static::$formDataSkipOnWedding;
        $test3FormData['data'] = json_encode($formData);
        $test3FormData['action'] = $actionPageSkipTo4;

        $form3_product_1 = $this->createTestForm($test3FormData);            // 3
        $form4_not_required = $this->createTestForm(static::$formDataSkipOnWedding);      // 4
        $form5_product_2 = $this->createTestForm(static::$formDataSkipOnWedding);         // 5

        // Given some test product data
        $productData = [
           'name' => 'ebay KFC powder 450g',
           'description' => 'Some good ol fried chickn powder',
           'state' => 'active',
           'sku' => 'fried-chkn-450g',
           'delivery_method' => Product::DELIVERY_METHODS['delivered'] | Product::DELIVERY_METHODS['collected'],
       ];

        //When I create a product 1 linked to form 3
        $productData['form_id'] = $form3_product_1->id;
        $product1 = $productManager->create(
            $this->business->id,
            $productData
        );

        // Make 2nd product a different form
        $productData['form_id'] = $form5_product_2->id;
        $product2 = $productManager->create(
            $this->business->id,
            $productData
        );

        // When we have not visited a form, expect the first form to be returned.
        $visitedFromIds = [];
        $submittedFormdata = [];
        $productIds = [$product1->id, $product2->id];
        $nextFormId = $this->formManager->getNextFormId(
            $businessId,
            $cartId = 1,
            $submittedFormdata,
            $productIds,
            $visitedFromIds,
        );

        // 1. Get form 1 - expect form 1
        $this->assertEquals($form1_required->id, $nextFormId);

        // 2. Submit form 1 - expect form 2
        $visitedFromIds = [$nextFormId];
        $submittedFormdata = $formData;
        $nextFormId = $this->formManager->getNextFormId(
            $businessId,
            $cartId = 1,
            $submittedFormdata,
            $productIds,
            $visitedFromIds,
        );
        $this->assertEquals($form2_required->id, $nextFormId);

        // 3. Submit form 2 - expect form 3
        $visitedFromIds[] = $nextFormId;
        $submittedFormdata = $formData;
        $nextFormId = $this->formManager->getNextFormId(
            $businessId,
            $cartId = 1,
            $submittedFormdata,
            $productIds,
            $visitedFromIds,
        );

        $this->assertEquals($form3_product_1->id, $nextFormId);

        // 4. Submit form 3 - logic executed IS -> Expect Form 4
        $visitedFromIds[] = $nextFormId;
        $submittedFormdata = $formData;

        // Submitted data is returned from the form renderer in a userData property.
        // The userData property is an array of values that have been selected.
        // In the case of text entry, it's the text the user entered.
        // From the docs: https://formbuilder.online/docs/formRender/actions/userData/
        //   UserData works for autocomplete, select, checkbox-group, radio-group, text, email, color, tel, number,
        //   hidden, date, textarea, textarea-tinymce.

        $submittedFormdata[3]->userData = ['wedding'];

        $nextFormId = $this->formManager->getNextFormId(
            $businessId,
            $cartId = 1,
            $submittedFormdata,
            $productIds,
            $visitedFromIds,
        );

        $this->assertEquals($form4_not_required->id, $nextFormId);

        // 5. Submit form 4 - Expect form 5
        $visitedFromIds[] = $nextFormId;
        $nextFormId = $this->formManager->getNextFormId(
            $businessId,
            $cartId = 1,
            $submittedFormdata,
            $productIds,
            $visitedFromIds,
        );
        $this->assertEquals($form5_product_2->id, $nextFormId);

        // 6. Submit form 5 - expect null.
        $visitedFromIds[] = $nextFormId;
        $nextFormId = $this->formManager->getNextFormId(
            $businessId,
            $cartId = 1,
            $submittedFormdata,
            $productIds,
            $visitedFromIds,
        );
        $this->assertEquals(null, $nextFormId);
    }

    /**
     * Test required responses are submitted (backend).
     */
    public function test_form_manager_requires_required_responses()
    {

        //Give I have a form
        $formData = $this->getRawTestFormData();
        $formData[3]->required = true;

        //When I submit it without required field
        $errors = $this->formResponseManager->validateForm($formData);

        // Then I get an error
        $this->assertEquals(['event-type' => 'required'], $errors);

        //Given I add the required field
        $formData[3]->userData = ['wedding'];

        //When I submit it
        $errors = $this->formResponseManager->validateForm($formData);

        //Then I get no errors
        $this->assertEquals([], $errors);

        //Given I add the invalid data
        $formData[3]->userData = ['some invalid data!'];

        //When I submit it
        $errors = $this->formResponseManager->validateIntegrity($formData);

        // Then I get an error
        $this->assertEquals(['event-type' => 'invalid'], $errors);

        //Given I add the invalid data
        $formData[3]->userData = ['wedding'];

        //When I submit it
        $errors = $this->formResponseManager->validateIntegrity($formData);

        // Then I get no error
        $this->assertEquals([], $errors);
    }

    /**
     * Test that required and invalid errors are detected.
     */
    public function test_form_manager_submit_validation()
    {
        $sessionId = '23423423';

        //Give I have a form that has invalid data in [4] and an unfilled reuqired in [3]
        $formData = $this->getRawTestFormData();
        $formData[3]->required = true;
        $formData[4]->required = true;

        $form = $this->createTestForm(['data' => $formData]);

        // Add the invalid response
        $formData[4]->userData = ['some invalid data!'];

        //When I submit the form without required field and an invalid field
        $response = $this->formResponseManager->submit($form->id, $formData, $sessionId);

        $expected = [
            'radio-group-1629966624736' => 'invalid',
            'event-type' => 'required',
        ];

        // Check validation and integrity errors triggeres.
        $this->assertEquals($expected, $response);

        // Check response / form wasn't cached.
        $cachedFormData = $this->formResponseManager->getCachedForm($form->id, $sessionId);

        $this->assertEquals($form->data, $cachedFormData);
    }

    /**
     * Test responses are stored in the cart (cached).
     */
    public function test_form_manager_save_caches_responses_in_cart_session()
    {
        $sessionId = '23423423';

        //Give I have a form that has invalid data in [4] and an unfilled reuqired in [3]
        $formData = $this->getRawTestFormData();
        $formData[3]->required = false;
        $formData[4]->required = true;

        $form = $this->createTestForm(['data' => $formData]);

        // Fetch raw form with no sumbissions, check is returned from DB.
        $cachedFormData = $this->formResponseManager->getCachedForm($form->id, $sessionId);

        $this->assertEquals($formData, $cachedFormData);

        // Add a the valid response
        $formData[4]->userData = ['option-1'];

        //When I submit with the required field
        $response = $this->formResponseManager->submit($form->id, $formData, $sessionId);

        // Check no validation or integrity errors are triggered.
        $this->assertEquals([], $response);

        // Check response / form was
        $cachedFormData = $this->formResponseManager->getCachedForm($form->id, $sessionId);
        $this->assertEquals($formData, $cachedFormData);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Test form manager can create a form from form data.
     */
    public function test_form_can_be_created()
    {
        $form = $this->createTestForm(static::$formDataSkipOnWedding);

        // Then I have the correct form info returned.
        $this->assertEquals(static::$formDataSkipOnWedding['name'], $form->name);

        $this->assertEquals($this->business->id, $form->owner->id);
    }

    /**
     * Test form manager can clone a form.
     */
    public function test_form_can_be_cloned()
    {
        $form = $this->createTestForm(static::$formDataSkipOnWedding);

        $clone = $this->formManager->clone($form);

        // Then I have the correct info on the original form.
        $this->assertEquals(static::$formDataSkipOnWedding['name'], $form->name);
        $this->assertEquals($this->business->id, $form->owner->id);

        // Test name has updated on clone
        $this->assertEquals(static::$formDataSkipOnWedding['name'].' '.$clone->id, $clone->name);

        // Check 2 forms exist
        $this->assertDatabaseCount('Forms', 2);
    }

    /**
     * Test that the form can be updated. and read.
     */
    public function test_form_can_be_updated_and_read()
    {
        $action_data = ['head' => 'bixqueer'];

        // Given I have a dummy form
        $form = $this->createTestForm(static::$formDataSkipOnWedding);

        // Given I want to update the form data
        $updatedFormData = [
            'name' => 'Bithex',
            'data' => 'pumpkin',
            'action' => $action_data,
            'settings' => ['rulez!'],
        ];

        // Then I update the form
        $updatedForm = $this->formManager->update($form, $updatedFormData);

        // Then check the form is updated.
        $this->assertEquals($updatedFormData['name'], $updatedForm->name);
        $this->assertEquals($updatedFormData['data'], $updatedForm->data);

        $this->assertIsArray($updatedFormData['action']);
        $this->arrayHasKey($updatedFormData['action'], 'head');
        $this->containsEqual('bixqueer');

        $this->assertIsArray($updatedFormData['settings']);
        $this->arrayHasKey($updatedFormData['settings'], 'rulez!');
    }

    public function test_get_all_forms_for_business()
    {
        $formManager = app()->make(FormManager::class);

        // Given I have a dummy form
        $form = $this->createTestForm(static::$formDataSkipOnWedding);

        // Given I have a second dummy form
        $form = $this->createTestForm(static::$formDataSkipOnWedding);

        // When  I fetch all the forms
        $forms = $formManager->all($this->business->id);

        // Then all the forms are in the database.
        $this->assertCount(2, $forms);
    }

    /**
     * Test that forms can be deleted.
     */
    public function test_form_can_be_deleted()
    {
        $formManager = app()->make(FormManager::class);

        // Given I create a form
        $form = $this->createTestForm(static::$formDataSkipOnWedding);

        // When I delete a form
        $formManager->delete($form->id);

        // Then there are no forms left.
        $this->assertCount(0, Form::all());
    }

    /**
     * Create a standard test form.
     *
     * @return Form|mixed
     */
    private function createTestForm($formData)
    {
        return $this->formManager->create(
            $this->business->id,
            $formData
        );
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

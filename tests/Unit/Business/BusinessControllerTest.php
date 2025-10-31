<?php

namespace Tests\Unit\Payment;

use App\Booking\Controllers\Frontend\BusinessController;
use Tests\TestCase;

class BusinessControllerTest extends TestCase
{
    /**
     * @test
     */
    public function bug_renders_business_form()
    {

        //Give some data.
        $controller = app()->make(BusinessController::class);
        $business = $this->getBusiness('Bugz R us');

        //When the controller constructs the view.
        $composedView = $controller->view($business->id);
        $viewData = $composedView->getData();

        //Thjen the data is present.
        $this->assertEquals('frontend.user.business', $composedView->name());
        $this->assertArrayHasKey('business', $viewData);
        $this->assertArrayHasKey('timezoneList', $viewData);
        $this->assertArrayHasKey('countries', $viewData);
        $this->assertArrayHasKey('paypal', $viewData);
        $this->assertArrayHasKey('bank', $viewData);
    }
}

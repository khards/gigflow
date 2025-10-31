<?php

//TODO - Move to domains.

namespace App\Booking\Controllers\Frontend;

use App\Booking\Business;
use App\Booking\Contracts\BusinessManager;
use App\Booking\Requests\Frontend\Business\Update as BusinessUpdateRequest;
use App\Booking\Requests\Frontend\Business\UpdatePayment as BusinessUpdatePaymentRequest;
use App\Domains\Payment\Services\PaymentService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Konekt\Address\Models\Country;
use stdClass;

/**
 * Class BusinessController.
 */
class BusinessController extends Controller
{
    // @todo - implement permissions / GUARD / OWNER !!!
    // @todo - implement permissions!!!

// @todo - implement permissions!!!

    // @todo - implement permissions!!!

    /**
     * @var BusinessManager
     */
    private $businessManager;

    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * BusinessController constructor.
     * @param BusinessManager $businessManager
     */
    public function __construct(BusinessManager $businessManager, PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        $this->businessManager = $businessManager;
    }

    /**
     * Update a business payments.
     *
     * @param Business $business
     * @param BusinessPaymentUpdateRequest $data
     */
    public function updatePayments(Business $business, BusinessUpdatePaymentRequest $data)
    {
        $fields = $data->only(['paypal.account', 'paypal.clientId', 'paypal.descriptor', 'paypal.currency', 'paypal.app_id', 'paypal.secret', 'paypal.webhook_id']);
        foreach ($fields['paypal'] as &$field) {
            $field = strip_tags($field);
        }
        $this->paymentService->createUpdate($business, 'paypal', $fields['paypal']);

        $fields = $data->only(['bank.name', 'bank.payee', 'bank.account', 'bank.sortcode']);
        foreach ($fields['bank'] as &$field) {
            $field = strip_tags($field);
        }
        $this->paymentService->createUpdate($business, 'bank', $fields['bank']);

        return redirect(route('frontend.user.business.view', ['businessId' => $business->id]).'#payments');
    }

    /**
     * Update a business.
     *
     * @param Business $business
     * @param BusinessUpdateRequest $data
     */
    public function update(Business $business, BusinessUpdateRequest $data)
    {
        $this->businessManager->update(
            $business,
            $data->only(['name', 'timezone', 'url', 'phone', 'email', 'currency'])
        );

        // To do move to address service.
        $fields = $data->only(['address.name', 'address.address', 'address.city', 'address.postalcode', 'address.country_id']);
        foreach ($fields['address'] as &$field) {
            $field = strip_tags($field);
        }
        $business->address()->update($fields['address']);

        return redirect(route('frontend.user.business.view', ['businessId' => $business->id]).'#information');
    }

    public function view($businessId)
    {
        $timezoneList = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        $currencyList = config('currencies');

        $countries = Country::all();
        $business = Business::findOrFail($businessId);
        $paypal = $this->paymentService->get($business, 'paypal');
        $bank = $this->paymentService->get($business, 'bank');

        return view('frontend.user.business',
            compact('business', 'timezoneList', 'countries', 'paypal', 'bank', 'currencyList'));
    }

    /**
     * @param  $request
     *
     * @return mixed
     * @throws \Throwable
     */
    public function index()
    {
        $business = $this->ifUserHasNoBusinessThenCreateOne();

        return redirect()->route('frontend.user.business.view', [
            'businessId' => $business->id,
        ]);
    }

    private function ifUserHasNoBusinessThenCreateOne()
    {
        $user = Auth::user();
        $business = $user->businesses()->first();

        if (! $business) {
            $businessName = 'My Business';
            $business = Business::create(['name' => $businessName]);
            $business->users()->attach($user);
        }

        return $business;
    }
}

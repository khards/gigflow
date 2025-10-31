<?php

namespace App\Domains\Checkout\Services;

use App\Booking\Address;
use App\Booking\Cart\Cart;
use App\Booking\Contracts\OrderProcessor;
use App\Booking\Customer;
use App\Domains\Address\Services\AddressService;
use App\Domains\Auth\Services\CustomerService;
use App\Domains\Billpayer\Services\BillpayerService;
use App\Domains\Checkout\CustomerForm\Address as AddressParser;
use App\Domains\Checkout\CustomerForm\BillPayer as BillpayerParser;
use App\Domains\Checkout\CustomerForm\Customer as CustomerFormParser;
use App\Domains\Email\Jobs\Quote\SendQuote;
use App\Domains\Form\Contracts\FormResponseManager;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Vanilo\Cart\Contracts\CartManager as CartManagerContract;
use Vanilo\Checkout\Facades\Checkout;
use Vanilo\Order\Models\Billpayer;
use Vanilo\Order\Models\Order;

class CheckoutService extends BaseService
{
    public function __construct(
        private OrderProcessor $orderProcessor,
        private FormResponseManager $formResponseManager,
        private CartManagerContract $cartManager,
        private CustomerService $customerService,
        private AddressService $addressService,
        private BillpayerService $billpayerService,
    ) {
    }

    /**
     * Process a new checkout request.
     *
     * @param array $request
     * @return Order
     * @throws \Throwable
     */
    public function processCheckout(array $request): Order
    {
        try {
            DB::beginTransaction();

            $this->addItemsToCart($request);

            $sessionId = $request['sessionId'] ?? $this->cartManager->getSessionId();
            $userResponses = $this->formResponseManager->getCachedForms($sessionId) ?? [];
            $customer = $this->createUpdateCustomer($userResponses);
            $address = $this->createBillingAddress($userResponses);
            $billpayer = $this->createBillpayer($userResponses, $address);
            $cart = $this->setupCart($customer);
            $this->setupCheckout($cart, $billpayer);

            $order = $this->orderProcessor->createOrderWithItems(
                $customer,
                $billpayer,
                $address,
                $this->cartManager,
                $request
            );

            if (! $order) {
                throw new RuntimeException('Could not create order');
            }

            // Save forms from cache to database.
            $this->formResponseManager->storeCachedForms($sessionId, $order);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::debug('Create booking failed due to:');
            Log::debug($exception);

            throw $exception;
        }
        DB::commit();

        SendQuote::dispatch(['orderId' => $order->id]);

        return $order;
    }

    /**
     * Parse user form and create new billing address.
     *
     * @param array $userResponses
     * @return Address
     * @throws Exception
     */
    private function createBillingAddress(array $userResponses): Address
    {
        $customerAddressDetails = (new AddressParser())->parse($userResponses);
        $customerAddressDetails['address_name'] = 'Billing Address';

        return $this->addressService->create($customerAddressDetails->toArray());
    }

    /**
     * @param array $userResponses
     * @param Address $address
     * @return Billpayer
     * @throws Exception
     */
    private function createBillpayer(array $userResponses, Address $address): Billpayer
    {
        $billPayerDetails = (new BillpayerParser())->parse($userResponses);

        return $this->billpayerService->create($address, $billPayerDetails->toArray());
    }

    /**
     * @param array $request
     */
    private function addItemsToCart(array $request): void
    {
        $errors = $this->cartManager->addItemsToCart(
            $request['products'],
            $request['start'],
            $request['end'],
            $request['location'],
            $request['businessId'],
        );

        if ($errors->count()) {
            throw new RuntimeException('Cant add items to cart '.var_export($errors, true));
        }
    }

    /**
     * Get the user details from the customer details form
     * Update existing customer and reactivate if nessacery
     * or create a new customer.
     *
     * @param array $userResponses
     * @return Customer
     * @throws Exception
     */
    private function createUpdateCustomer(array $userResponses): mixed
    {
        $customerFormParser = new CustomerFormParser();
        $customerDetails = $customerFormParser->parse($userResponses);

        return $this->customerService->createUpdate($customerDetails->toArray());
    }

    /**
     * @param Customer $customer
     * @return Cart
     */
    private function setupCart(Customer $customer): Cart
    {
        $cart = $this->cartManager->getCart();
        $cart->setUser($customer->id);

        return $cart;
    }

    /**
     * @param Cart $cart
     * @param Billpayer $billpayer
     */
    private function setupCheckout(Cart $cart, Billpayer $billpayer): void
    {
        Checkout::setCart($cart);
        Checkout::setBillpayer($billpayer);
    }
}

<?php

namespace App\Booking\Controllers\Api;

use App\Booking\Contracts\OrderProcessor;
use App\Booking\Contracts\ProductManager;
use App\Booking\Requests\Api\Cart\UpdateRequest;
use App\Booking\Resources\Api\CartResource;
use App\Domains\Checkout\CustomerForm\Address as AddressParser;
use App\Domains\Checkout\CustomerForm\BillPayer as BillpayerParser;
use App\Domains\Checkout\CustomerForm\Customer as CustomerFormParser;
use App\Domains\Checkout\CustomerForm\Exceptions\CustomerFormInternalValidationException;
use App\Domains\Form\Contracts\FormManager;
use App\Domains\Form\Contracts\FormResponseManager;
use App\Domains\Payment\Services\DepositService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Vanilo\Cart\Contracts\CartManager as CartManagerContract;

class Cart extends Controller
{
    /**
     * Create a new Cart controller instance.
     *
     * @param ProductManager      $productManager
     * @param OrderProcessor      $orderProcessor
     * @param FormManager         $formManager
     * @param FormResponseManager $formResponseManager
     * @param DepositService      $depositService
     * @param CartManagerContract $cartManager
     */
    public function __construct(
        private ProductManager $productManager,
        private OrderProcessor $orderProcessor,
        private FormManager $formManager,
        private FormResponseManager $formResponseManager,
        private DepositService $depositService,
        private CartManagerContract $cartManager
    ) {
    }

    /**
     * Validate items, cache form responses, return display info (total price).
     *
     * > This validates cart items and stored them in the database, these carts are never used however the validation is.
     * > The form responses are  cached from the update API call
     * > A session id is returned to keep track of form responses.
     * > Correct pricing and availability are returned for display.
     * > Errors are returned.
     * @param UpdateRequest $request
     * @return array
     */
    public function update(UpdateRequest $request): CartResource
    {
        // Start session (if needed)
        $sessionId = $request->get('sessionId') ?? $this->cartManager->getSessionId();
        $errors = $this->cartManager->addItemsToCart(
            $request->get('products', []),
            $request->get('start'),
            $request->get('end'),
            $request->get('location'),
            $request->get('businessId'),
        );

        $errors = $errors->merge($this->validateFormItems($request, $sessionId, $errors));
        $errors = $this->submitForm($request, $sessionId, $errors);

        return $this->updateResponse($sessionId, $request, $errors);
    }

    // Validate form items, uses config in checkout.php and parsing for any fixed items such as phone length
    // restrictions
    private function validateFormItems($request, $sessionId, $errors) {

        $newErrors = [];

        // May need to check if it's a 'required form' ?

        if ($currentFormId = $request->get('currentFormId')) {
            if ($submittedFormData = json_decode($request->get('formData'))) {

                $payload = [$currentFormId => $submittedFormData];

                try {
                    (new BillpayerParser())->parse($payload);
                    (new CustomerFormParser())->parse($payload);
                    (new AddressParser())->parse($payload);
                } catch (CustomerFormInternalValidationException $error) {
                    $newErrors[$error->key] = $error->data;
                }
            }
        }

        if($newErrors) {
            $newErrors['form'] = $newErrors;
        }

        return collect($newErrors);
    }
    /**
     * Generate update response.
     *
     * @param string $sessionId
     * @param UpdateRequest $request
     * @param Collection $errors
     *
     * @return CartResource
     */
    private function updateResponse(string $sessionId, UpdateRequest $request, Collection $errors): CartResource
    {
        $dispatchPrice = $this->cartManager->getDeliveryCost($request->only('start', 'end', 'location'));
        $totalProductPrice = $this->cartManager->getTotalProductPrices($request->only('start', 'end', 'location'));
        $adjustments = $this->cartManager->getAdjustments();
        $totalPrice = round($dispatchPrice + $totalProductPrice + $adjustments);
        $deposit = $this->depositService->calculateDeposit($totalPrice);
        $totalLines = (int) $this->cartManager->getItems()->reduce(function ($carry, $item) {
            return $carry + $item->getQuantity();
        });
        $totalItems = count($this->cartManager->getItems());
        $reference = (string) $this->cartManager->model()->id;
        $formData = [];

        // If we have form validation errors, then return the submitted form and stay on current form.
        if ($errors->get('form')) {
            $formId = $request->get('currentFormId');
            $formData = json_decode($request->get('formData'));
        } else {
            if ($formId = $this->getNextFormIdFromAction($request, $sessionId)) {
                $formData = $this->formResponseManager->getCachedForm($formId, $sessionId);
            }
        }

        return CartResource::make(compact([
            'errors',
            'totalLines',
            'totalItems',
            'reference',        //Cart reference
            'adjustments',
            'totalPrice',
            'dispatchPrice',
            'deposit',

            'sessionId',
            'formId',
            'formData',
        ]));
    }

    /**
     * Get visited form ID's.
     *
     * @param string $sessionId
     *
     * @return array
     */
    private function visitedFormIds(string $sessionId): array
    {
        $cacheKey = $sessionId.'_form_ids_visited';

        return Cache::get($cacheKey, []);
    }

    /**
     * Store visited Form Ids.
     *
     * @param string $sessionId
     * @param array  $visitedFormIds
     */
    private function setVisitedFormIds(string $sessionId, array $visitedFormIds): void
    {
        $cacheKey = $sessionId.'_form_ids_visited';
        $seconds = 60 * 60 * 12; /* hours */

        // Save the form visited stack.
        Cache::put($cacheKey, $visitedFormIds, $seconds);
    }

    /**
     * Calculate the next form ID from
     *  the given action
     *  the request form data (if present)
     *  the previous pages visited.
     *
     * @param UpdateRequest $request
     * @param string        $sessionId
     *
     * @return int|null
     */
    private function getNextFormIdFromAction(UpdateRequest $request, string $sessionId): int|null
    {
        $productIds = $this->cartManager->getItems()->pluck('id')->toArray();

        // $submittedFormData can be null when not on a form page.
        $submittedFormData = json_decode($request->get('formData', '[]')); //Must pass into method!
        if (! $submittedFormData) {
            $submittedFormData = [];
        }

        $currentFormId = $request->get('currentFormId');
        $businessId = (int) $request->get('businessId');

        $visitedFormIds = $this->visitedFormIds($sessionId);

        $formId = null;
        $navigationAction = $request->get('navAction');
        if ($navigationAction == 'backward') {

            //Next form id is last visited! (If any)
            if (count($visitedFormIds)) {
                // Get the last visited form id to return
                $formId = end($visitedFormIds);
                // Remove it from the stack
                unset($visitedFormIds[count($visitedFormIds) - 1]);
            }
        } else { //it's forward.
            if ($currentFormId) { // prevent storing null (no form submitted) in history.
                // Store currently visited form in stack.
                // Don't worry about next page, that will be calculated again and cached returned.
                $visitedFormIds[] = $currentFormId;
            }
        }

        $this->setVisitedFormIds($sessionId, $visitedFormIds);

        // If we were not submitting a form, then return the first form (cached)
        // This occurs when we have pressed back away from form pages, then re-navigate to them to edit.
        if (! $currentFormId) {
            return $this->formManager->getNextFormId(
                $businessId,
                $sessionId,
                $submittedFormData,
                $productIds,
                []
            );
        }

        if (! $formId) {
            $formId = $this->formManager->getNextFormId(
                $businessId,
                $sessionId,
                $submittedFormData,
                $productIds,
                $visitedFormIds
            );
        }

        return $formId;
    }

    /**
     * Form submission, is cached against cart session ID.
     *
     * @param UpdateRequest $request
     * @param mixed $sessionId
     * @param Collection $errors
     * @return Collection
     */
    private function submitForm(UpdateRequest $request, mixed $sessionId, Collection $errors): Collection
    {
        if ($currentFormId = $request->get('currentFormId')) {
            if ($submittedFormData = json_decode($request->get('formData'))) {
                if ($formSubmitErrors = $this->formResponseManager->submit($currentFormId, $submittedFormData, $sessionId)) {
                    $errors['form'] = $formSubmitErrors;
                }
            }
        }

        return $errors;
    }
}

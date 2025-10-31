<?php

namespace App\Booking\Cart;

use App\Booking\Availability\Checker\AvailabilityData;
use App\Booking\Availability\Checker\AvailabilityRequest;
use App\Booking\Availability\Checker\AvailabilityResponse;
use App\Booking\Availability\Checker\Checker;
use App\Booking\Business;
use App\Booking\Product;
use App\Booking\Services\DistanceService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Vanilo\Cart\CartManager as VanilloCartManager;
use Vanilo\Cart\Contracts\CartItem;
use Vanilo\Cart\Contracts\CartManager as CartManagerContract;
use Vanilo\Contracts\Buyable;

class CartManager extends VanilloCartManager
{
    /** @var DistanceService */
    private $distanceService;

    /** @var Checker */
    protected $checker;

    /** @var CartManagerContract */
    protected $cart;

    /** @var DispatchCost */
    private $shippingCost;

    public function __construct()
    {
        parent::__construct();

        $this->checker = new Checker();
        $this->cart = $this->findOrCreateCart();
        $this->distanceService = new DistanceService();
        $this->shippingCost = new DispatchCost($this);
    }

    /**
     * Get new session ID.
     *
     * @return string
     */
    public function getSessionId(): string
    {
        return rand(1000, 9999).uniqid();
    }

    /**
     * Add items to cart.
     *
     * @param array  $products
     * @param string $start
     * @param string $end
     * @param string $location
     * @param int    $businessId
     *
     * @return Collection
     */
    public function addItemsToCart(array $products, string $start, string $end, string $location, int $businessId): Collection
    {
        $errors = collect();

        // Note: This only needs to happen on the final form submission as the cart is currently stored in php session
        // against a user (if logged in). We use during booking to find errors.
        foreach ($products as $item) {
            $product = Product::find($item['id']);

            try {
                $this->addItem($product, $item['quantity'], [
                    'attributes' => ['start' => $start, 'end' => $end, 'location' => $location, 'businessId' => $businessId],
                ]);
            } catch (ProductUnavailableException $e) {
                $errors[$product->id] = $e->getMessage();
            }
        }

        return $errors;
    }

    /**
     * @inheritDoc
     */
    public function addItem(Buyable $product, $qty = 1, $params = []): CartItem
    {
        $cartItemAttributes = $params['attributes'];
        //@TODO - remove fail and replave with an exception/error handler
        $business = Business::findOrFail($cartItemAttributes['businessId']);
        $requestData = [
            'business' => $business,
            'start' => $cartItemAttributes['start'] ?? null,
            'end' => $cartItemAttributes['end'] ?? null,
            'location' => $cartItemAttributes['location'] ?? null,
            'active' => true,
            'product' => $product,
            'quantity' => $qty,
        ];
        $request = new AvailabilityRequest($requestData);
        $availabilityData = new AvailabilityData($request, new AvailabilityResponse());
        $result = $this->checker->checkAvailability($availabilityData);

        if ($result->response->status === AvailabilityResponse::STATUS_UNAVAILABLE) {
            throw new ProductUnavailableException($result);
        }

        return $this->cart->addItem($product, $qty, $params);
    }

    /**
     * Return sum of all product prices for given period
     *   - no discount's etc. applied.
     *
     * @param $attributes
     * @return array
     */
    public function getProductPrices($attributes)
    {
        $prices = [];
        foreach ($this->cart->getItems() as $item) {
            /* @var CartItem $item */
            $prices[$item->getBuyable()->getId()] = $item->getBuyable()->getPrice($attributes) * $item->getQuantity();
        }

        return $prices;
    }

    /**
     * Get the total delivery cost.
     *
     * @param $attributes
     * @return float|int
     */
    public function getDeliveryCost($attributes)
    {
        return $this->shippingCost->getDispatchCost($attributes);
    }

    //@TODO - discounts!
    public function getAdjustments()
    {
        return 0;
    }

    /**
     * Get the total price.
     *
     * @param $attributes
     * @return float|int|mixed|void
     */
    public function getTotalProductPrices($attributes)
    {
        $price = 0.0;
        foreach ($this->getProductPrices($attributes) as $productPrice) {
            $price += $productPrice;
        }

        return round($price);
    }

    /**
     * @inheritDoc
     */
    public function setUser($user)
    {
        if ($this->exists()) {
            $this->cart->setUser($user);
            $this->cart->save();
            $this->cart->load('user'); //'user'
        }
    }

    /**
     * @return CartManagerContract
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @return DistanceService
     */
    public function getDistanceService(): DistanceService
    {
        return $this->distanceService;
    }
}

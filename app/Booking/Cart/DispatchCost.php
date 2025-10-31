<?php

namespace App\Booking\Cart;

use App\Booking\Address;
use Vanilo\Cart\Contracts\CartItem;
use Vanilo\Contracts\Buyable;

/**
 * @todo - unit test this, it's important!!
 *
 * Methods for calculating:
 *
 * Variable "delivery" cost (not the shipping/postage cost)
 *      This is when we charge for delivery by miles, kilometers or time / per booking.
 *
 * Fixed shipping cost.
 *      This is when we post an item to people.
 *
 * Class DeliveryCost
 */
class DispatchCost
{
    private $cartManager;

    public function __construct(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
    }

    /**
     * Calculate the total combined shipping & delivery cost for all items in the basket.
     *
     * @param array $attributes
     * @return float|int
     */
    public function getDispatchCost(array $attributes)
    {

// There is a potential issue here:
        // @TODO
        // If you have shipped products, say 1 case of glowsticks that cost £10 to devlier
        // Then you have a travelling charge of 23.00
        // Only the travelling charge of 23.00 is taken into account and the customer get's free shipping.
        // This either needs to be optional or take decision to treat service + product differenty.

        //1. Use the largest shipping cost          <---- MVP! Works for me
        //2. (2) Add all shipping costs
        //3. (2) Free shipping over x ammount

        $totalShippingCharge = 0;
        $highestOrderCharge = 0;
        $highestShippingCharge = 0;

        foreach ($this->cartManager->getCart()->getItems() as $item) {
            /** @var CartItem $item */
            $product = $item->getBuyable();

            if ($product->isCollected()) {
                // Can we charge for collection ??
            }

            // Get shipping rate based on location, from delivery address!
            if ($product->isShipped()) {
                // HTF do I know if this is posted or collected for items that support both?
                //
                // Will have to supply deliver / collected option when added a delivery/shipped product
                //
                // If the delivery product is sold with a service, then there should be an option to have it deliverd with the service.
                // For example glowstick's can be shipped, or delivered with a disco

                $shippingCharge = $this->calculateShippingCost($product, $attributes, $item->getQuantity());
                if ($shippingCharge > $highestShippingCharge) {
                    $highestShippingCharge = $shippingCharge;
                }
                $totalShippingCharge += $shippingCharge;
            }

            if ($product->isDelivered()) {
                $deliveryCharge = $this->calculateDeliveryCost($product, $attributes, $item->getQuantity());
                if ($deliveryCharge > $highestOrderCharge) {
                    $highestOrderCharge = $deliveryCharge;
                }
            }
        } // end each cart item

        $totalShippingCharge += $highestOrderCharge;

        return $totalShippingCharge;
    }

    /**
     * Calculate an individual product's delivery cost based on location.
     *
     * @param Buyable $product
     * @param string $location
     * @return float
     */
    public function getProductDeliveryCharge(Buyable $product, string $location): float
    {
        /**
         * @var Address $origin
         */
        $origin = $product->business->address;

        $response = $this->cartManager->getDistanceService()->getDistance(
            $origin->postalcode,
            $origin->formatSingleLine(),
            $location
        );

        $charge = $product->settings->get('delivery.delivered.charge');
        $per = $product->settings->get('delivery.delivered.per');
        $over = $product->settings->get('delivery.delivered.over');

        $finalCharge = 0;
        switch ($per) {
            case 'kilometer':
                if(($distance = $response->getKilometers()) > $over) {
                    $finalCharge = ($distance * $charge) / 100;
                }
                break;

            case 'mile':
                if(($distance = $response->getMiles()) > $over) {
                    $finalCharge = ($distance * $charge) / 100;
                }
                break;
        }
        // Round up to the nearest pound, euro, dollar, etc..
        return round($finalCharge, 0, PHP_ROUND_HALF_UP);
    }

    /**
     * Calculate a single products delivery cost.
     *
     * @param  Buyable  $product
     * @param array $attributes
     * @param int $quantity
     * @return float
     */
    private function calculateDeliveryCost(Buyable $product, array $attributes, int $quantity): float
    {
        if (! isset($attributes['location'])) {
        }

        $productCharge = $this->getProductDeliveryCharge($product, $attributes['location']);

        $per = $product->settings->get('delivery.delivered.per');
        switch ($per) {
            case 'kilometer':
            case 'mile':
            case 'order':
                break;

            case 'item':
                $productCharge = (float) ($productCharge * $quantity);
                break;
        }

        return $productCharge;
    }

    /**
     * Calculate a single products shipping cost.
     *
     * @param  Buyable  $product
     * @param array $attributes
     * @param int $quantity
     * @return float
     */
    private function calculateShippingCost(Buyable $product, array $attributes, int $quantity): float
    {
        $per = $product->settings->get('delivery.shipped.per');
        $productCharge = $product->settings->get('delivery.shipped.price');

        switch ($per) {
            case 'order':
                break;

            case 'item':
                $productCharge = (float) ($productCharge * $quantity);
                break;
        }

        return $productCharge;
    }
}

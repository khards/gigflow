<?php

namespace App\Booking\Cart;

use Vanilo\Cart\Contracts\CartItem;
use Vanilo\Cart\Models\Cart as VanilloCart;
use Vanilo\Contracts\Buyable;

class Cart extends VanilloCart
{
    /**
     * @inheritDoc
     */
    public function addItem(Buyable $product, $qty = 1, $params = []): CartItem
    {
        $item = $this->items()->ofCart($this)->byProduct($product)->first();

        if ($item) {
            $item->quantity += $qty;
            $item->save();
        } else {
            $defaultAttributes = $this->getDefaultCartItemAttributes($product, $qty);
            $extraAttributes = $this->getExtraProductMergeAttributes($product);
            $attributes = $params['attributes'] ?? [];
            $fields = array_merge($defaultAttributes, $extraAttributes, $attributes);

            $item = $this->items()->create($fields);
        }

        $this->load('items');

        return $item;
    }
}

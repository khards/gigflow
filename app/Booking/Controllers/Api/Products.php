<?php

namespace App\Booking\Controllers\Api;

use App\Booking\Contracts\ProductManager;
use App\Booking\Requests\Api\Product\ProductsGetRequest;
use App\Booking\Resources\Api\ProductsResource;
use App\Http\Controllers\Controller;

class Products extends Controller
{
    private ProductManager $productManager;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductManager $productManager)
    {
        $this->productManager = $productManager;
    }

    /**
     * @param ProductsGetRequest $request
     * @return ProductsResource
     */
    public function get(ProductsGetRequest $request): ProductsResource
    {
        $business = $request->business;
        $availabilityData = $this->productManager->all($business, $request->all());

        if ($request->get('available') === '1') {
            $availabilityData = $availabilityData['available'];
        }
        else if ($request->get('available') === '0') {
            $availabilityData = $availabilityData['unavailable'];
        }
        else {
            $availabilityData = array_merge($availabilityData['available'], $availabilityData['unavailable']);
        }

        return ProductsResource::make($availabilityData);
    }
}

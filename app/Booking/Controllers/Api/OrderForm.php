<?php

namespace App\Booking\Controllers\Api;

use App\Booking\Contracts\ProductManager;
use App\Booking\Requests\Api\Product\ProductsGetRequest;
use App\Booking\Resources\Api\OrderFormResource;
use App\Booking\Resources\Api\ProductsResource;
use App\Http\Controllers\Controller;

class OrderForm extends Controller
{
    private ProductManager $productManager;

    /**
     * Create a new controller instance.
     *
     * @param ProductManager $productManager
     */
    public function __construct(ProductManager $productManager)
    {
        $this->productManager = $productManager;
    }

    /**
     * Gets all products for a given business.
     *
     * @param ProductsGetRequest $request
     *
     * @return OrderFormResource
     */
    public function get(ProductsGetRequest $request)
    {
        $business = $request->business;
        $availabilityData = $this->productManager->all($business, $request->all());

        return OrderFormResource::make($availabilityData);
    }
}

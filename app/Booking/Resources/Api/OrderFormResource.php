<?php

namespace App\Booking\Resources\Api;

use App\Booking\Availability\Checker\AvailabilityData;
use App\Booking\Business;
use App\Booking\Product\Exceptions\ProductPriceNoncalculable;
use App\Booking\Product\Exceptions\ProductPriceNotAvailable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderFormResource extends JsonResource
{
    const DEBUG = false;

    /**
     * Transform the resource into an array.
     *
     * Note: This only supports parent child relationship with product variants
     *
     * @TODO - Is this tested ?????
     *
     * @param  $request
     * @return array
     * @throws ProductPriceNoncalculable
     * @throws ProductPriceNotAvailable
     */
    public function toArray($request): array
    {
        $productTree = [];
        $bookingExtra = [];

        if ($request->get('available') === '1') {
            $availabilityDatas = $this->resource['available'];
        }
        else if ($request->get('available') === '0') {
            $availabilityDatas = $this->resource['unavailable'];
        }
        else {
            $availabilityDatas = array_merge($this->resource['available'], $this->resource['unavailable']);
        }

        foreach ($availabilityDatas as $availabilityData) {
            /** @var AvailabilityData $availabilityData */
            $parentProduct = $availabilityData->request->product;
            $children = $parentProduct->variations()->get();
            $isParent = $parentProduct->parent()->first() === null;
            $hasChildProducts = count($children) > 0;
            if ($isParent) {
                $productTree[$parentProduct->id] = $this->products($availabilityData, $request);
            }

            if ($hasChildProducts) {
                foreach ($children as $child) {
                    $searchable = [];
                    foreach ($availabilityDatas as $item) {
                        $searchable[$item->request->product->id] = $item;
                    }

                    // If it's available
                    if (isset($searchable[$child->id])) {
                        $childAvailabilityData = $searchable[$child->id];
                        $productTree[$parentProduct->id]['products'][] = $this->products($childAvailabilityData, $request);
                    }
                }
            }
            if(self::DEBUG) {
                $bookingExtra[$parentProduct->id] = $availabilityData->response;
            }
        }

        $orderForm['products'] = $productTree;
        $orderForm['info'] = $this->getInfo();

        $business = Business::find($request->get('business'));
        $currencies = collect(config('currencies'));
        $currency = $currencies->where('code', $business->currency)->first();
        $orderForm['currency_symbol'] = ($currency['symbol']);//htmlentities

        if(self::DEBUG) {
            $orderForm['booking_extra'] = $bookingExtra;
        }

        return $orderForm;
    }

    /**
     * @param AvailabilityData $availabilityData
     * @param Request $request
     * @return array
     * @throws ProductPriceNoncalculable
     * @throws ProductPriceNotAvailable
     */
    private function products(AvailabilityData $availabilityData, Request $request): array
    {
        $product = $availabilityData->request->product;

        //Optional includes
        $includePrice = Str::contains($request->get('opt_fields'), 'price');

        $data = [
            'force' => $product->is_required,
            'extra' => $product->is_addon,
            'id' => $product->id,
            'title' => $product->name,
            'description' => $product->description,
            'type' => $product->type,
            'image' => $product->settings->get(['image_path']),
            'url' => $product->settings->get(['url']),
            'quantityAvailable' => $availabilityData->response->quantityAvailable,
            'status' => $availabilityData->response->status,
        ];

        // Optional includes
        if ($includePrice) {
            try {
                $price = $product->getPrice([
                    'start' => $request->get('start'),
                    'end' => $request->get('end'),
                ]);
                $data['price'] = number_format($price, 2, '.', '');
            }
            catch (\Exception $e) {
                Log::error("Error with product {$product->id} price " . $e->getMessage());
            }
        }

        return $data;
    }

    private function getInfo(): array
    {
        $info = [];
        foreach (array_merge($this->resource['unavailable'], $this->resource['available']) as $availabilityData) {
            if ($availabilityData->request->product->isParent()) {
                if ($availabilityData->response->info) {
                    $info[$availabilityData->request->product->id] = $availabilityData->response->info;
                }
            }
        }
        return $info;
    }
}

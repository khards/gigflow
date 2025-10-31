<?php

namespace App\Booking\Resources\Api;

use App\Booking\Availability\Checker\AvailabilityData;
use App\Booking\Availability\Checker\AvailabilityResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ProductsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $break = null;
        $products = [];
        foreach ($this->resource as $availabilityData) {
            /* @var AvailabilityResponse $availabilityData */
            $products[] = $this->productAttributes($availabilityData, $request);
        }

        return $products;
    }

    /**
     * @param AvailabilityData $availabilityData
     * @return array
     */
    private function productAttributes(AvailabilityData $availabilityData, Request $request)
    {

        //Optional includes
        $includePrice = Str::contains($request->get('opt_fields'), 'price');

        if ($includePrice) {
            $price = $availabilityData->request->product->getPrice([
                'start' => $request->get('start'),
                'end' => $request->get('end'),

                //@todo Timezone!!!

            ]);
        }

        $data = [

            'id' => $availabilityData->request->product->id,
            'name' => $availabilityData->request->product->name,
            'description' => $availabilityData->request->product->description,
            'type' => $availabilityData->request->product->type,
            'imagePath' => $availabilityData->request->product->settings->get(['image_path']),
            'url' => $availabilityData->request->product->settings->get(['url']),
            'quantityAvailable' => $availabilityData->response->quantityAvailable, // 0+
            'status' => $availabilityData->response->status, //available/unavailable
        ];

        // Optional includes
        if ($includePrice) {
            $data['price'] = number_format((float) $price, 2, '.', '');
        }

        return $data;
    }
}

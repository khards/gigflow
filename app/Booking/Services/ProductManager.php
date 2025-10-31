<?php

namespace App\Booking\Services;

use App\Booking\Availability\Checker\AvailabilityData;
use App\Booking\Availability\Checker\AvailabilityRequest;
use App\Booking\Availability\Checker\AvailabilityResponse;
use App\Booking\Availability\Checker\Checker;
use App\Booking\Availability\Schedule;
use App\Booking\Business;
use App\Booking\Contracts\ProductManager as ProductManagerContract;
use App\Booking\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ProductManager implements ProductManagerContract
{
    /**
     * Create single product.
     *
     * @param mixed $business
     * @param array $productDetails
     * @return Product
     */
    public function create($business, array $productDetails): Product
    {
        if (! is_object($business)) {
            $business = Business::findOrFail($business);
        }

        $product = Product::create([
            'name' => $productDetails['name'],
            'description' => $productDetails['description'],
            'state' => $productDetails['state'],
            'sku' => $productDetails['sku'],
            'form_id' => $productDetails['form_id'] ?? null,
            'owner_type' => $business->getMorphClass(),
            'owner_id' => $business->id,
        ]);

        return $this->update($product, $productDetails);
    }

    /**
     * @param $id
     * @return Product
     */
    public function read($id)
    {
        $product = Product::with(['schedules', 'parent'])->findOrFail($id);

        return $this->prepareProduct($product);
    }

    /**
     * Prepare product model for use.
     *
     * @param Product $product
     * @return Product
     */
    private function prepareProduct(Product $product)
    {

        // Adjust prices to floating point
        $product->price_fixed_price = $product->price_fixed_price / 100;
        $product->travelling_value = $product->travelling_value / 100;

        // Adjust delivery prices to floating point
        $delivery = $product->settings->get('delivery');
        $delivery['delivered']['charge'] = isset($delivery['delivered']['charge']) ? $delivery['delivered']['charge'] / 100 : 0;
        $delivery['shipped']['price'] = isset($delivery['shipped']['price']) ? $delivery['shipped']['price'] / 100 : 0;
        $product->settings->set('delivery', $delivery);

        return $product;
    }

    /**
     * Raw products - No availability checking.
     *
     * @param Business $business
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function raw(Business $business, $filters = [])
    {
        return $business->products()->get();
    }

    /**
     * Get all products for a business, optionally filter.
     *
     * @param Business $business
     * @param array $filter
     * @return AvailabilityData[]
     * @todo    - cache the results
     *          - cache invalidation will work from events:
     *              booking: update, creation
     *              schedule: update
     *              product: update
     */
    public function all($business, $filter = []): array
    {
        if (! is_object($business)) {
            $business = Business::findOrFail($business);
        }

        $requestData = [
            'business' => $business,
            'start' => $filter['start'] ?? null,
            'end' => $filter['end'] ?? null,
            'location' => $filter['location'] ?? null,
            'timezone' => $filter['timezone'] ?? null,
        ];

        // 'active' is tri-state true, false, null.
        if ($state = $filter['state'] ?? null) {
            $requestData['active'] = $state === 'active';
        }

        $checker = new Checker();

        $results = [
            'available' => [],
            'unavailable' => [],
        ];

        foreach ($business->products as $product) {
            $requestData['product'] = $product;
            $request = new AvailabilityRequest($requestData);
            $availabilityData = new AvailabilityData($request, new AvailabilityResponse());
            $availabilityData = $checker->checkAvailability($availabilityData);

            if ($availabilityData->response->status === AvailabilityResponse::STATUS_AVAILABLE) {
                $results['available'][] = $availabilityData;
            } else {
                $results['unavailable'][] = $availabilityData;
            }
        }

        return $results;
    }

    /**
     * @param Product $product
     * @param array $productDetails
     * @return Product
     */
    public function update(Product $product, array $productDetails): Product
    {
        if (! is_object($product)) {
            $product = $this->read($product);
        }

        if (isset($productDetails['image'])) {
            if ($path = $product->settings->get('image_path')) {
                $deleted = Storage::delete($path);
            }

            $imagePath = '/'.$productDetails['image']->store('public/products/'.$product->id);
            $product->settings->set(['image_path' => $imagePath]);
        }

        if (isset($productDetails['form'])) {
            $product->form_id = $productDetails['form'];
        }

        if (isset($productDetails['form_id'])) {
            $product->form_id = $productDetails['form_id'];
        }

        if (isset($productDetails['url'])) {
            $product->settings->set(['url' => $productDetails['url']]);
        }
        if (isset($productDetails['setup_time'])) {
            $product->setup_time = $productDetails['setup_time'];
        }

        if (isset($productDetails['name'])) {
            $product->name = $productDetails['name'];
        }
        if (isset($productDetails['description'])) {
            $product->description = $productDetails['description'];
        }
        if (isset($productDetails['state'])) {
            $product->state = $productDetails['state'];
        }
        if (isset($productDetails['is_addon'])) {
            $product->is_addon = $productDetails['is_addon'];
        }
        if (isset($productDetails['is_required'])) {
            $product->is_required = $productDetails['is_required'];
        }
        $product->type = $productDetails['type'] ?? 'service';
        $product->price_type = $productDetails['price_type'] ?? 'fixed';
        if (isset($productDetails['price_fixed_price'])) {
            $product->price_fixed_price = $productDetails['price_fixed_price'] * 100; //Convert to pennies.
        }
        if (isset($productDetails['staff_quantity'])) {
            $product->staff_quantity = $productDetails['staff_quantity'];
        }
        if (isset($productDetails['availability_type'])) {
            $product->availability_type = $productDetails['availability_type'];
        }
        if (isset($productDetails['availability_schedule'])) {
            $product->availability_schedule = $productDetails['availability_schedule'];
        }
        if (isset($productDetails['available_quantity'])) {
            $product->available_quantity = $productDetails['available_quantity'];
        }
        if (isset($productDetails['travelling_limit'])) {
            $product->travelling_limit = $productDetails['travelling_limit'];
        }
        if (isset($productDetails['travelling_value'])) {
            $product->travelling_value = (int) ($productDetails['travelling_value'] * 100);
        }
        if (isset($productDetails['travelling_type'])) {
            $product->travelling_type = $productDetails['travelling_type'];
        }
        if (isset($productDetails['delivery'])) {
            $delivery = $productDetails['delivery'];
        }
        if (isset($productDetails['delivery']['delivered']['charge'])) {
            $delivery['delivered']['charge'] = ($productDetails['delivery']['delivered']['charge']) ? (int) ($productDetails['delivery']['delivered']['charge'] * 100) : 0;
        }
        if (isset($productDetails['delivery']['delivered']['over'])) {
            $delivery['delivered']['over'] = ($productDetails['delivery']['delivered']['over']) ? (int) ($productDetails['delivery']['delivered']['over']) : 0;
        }

        // Delivery charges
        $delivery['delivered']['per'] = $productDetails['delivery']['delivered']['per'] ?? 'mile';
        $delivery['shipped']['price'] = isset($productDetails['delivery']['shipped']['price']) ? (int) ($productDetails['delivery']['shipped']['price'] * 100) : 0;
        $delivery['shipped']['per'] = $productDetails['delivery']['shipped']['per'] ?? 'order';
        $product->settings->set(['delivery' => $delivery]);

        // Advance charges.
        if (isset($productDetails['advance_charges'])) {
            $product->settings->set('advance_charges', $productDetails['advance_charges']);
        }

        // Over hours charge
        if (isset($productDetails['extra_hours_charge_max_hours'])) {
            $product->settings->set('extra_hours_charge_max_hours', $productDetails['extra_hours_charge_max_hours']);
        }

        // Block same day bookings
        if (isset($productDetails['block_same_day_bookings'])) {
            $product->settings->set('block_same_day_bookings', $productDetails['block_same_day_bookings']);
        }

        // block_number_days_future
        if (isset($productDetails['block_number_days_future'])) {
            $product->settings->set('block_number_days_future', $productDetails['block_number_days_future']);
        }


        // Blocked post codes
        if (isset($productDetails['blocked_postcodes'])) {
            $product->settings->set('blocked_postcodes', $productDetails['blocked_postcodes']);
        }

        // Delivery method
        if (isset($productDetails['delivery_method'])) {
            $product->delivery_method = $productDetails['delivery_method'];
        }

        $product->sku = $productDetails['sku'] ?? $product->sku;
        $product->slug = $productDetails['slug'] ?? $product->slug;

        $product->save();

        if (($productDetails['price_type'] ?? null) === 'scheduled') {
            $this->replaceSchedules($product, $productDetails['_scheduled_prices']);
        }

        return $this->prepareProduct($product);
    }

    /**
     * @param $product
     * @param array $scheduledPrices
     */
    protected function replaceSchedules($product, array $scheduledPrices)
    {
        if (! $scheduledPrices) {
            return;
        }

        //Remove old schedules..
        $product->schedules()->detach();

        //Iterate the schedules and add them to the product (schedule_map)
        foreach ($scheduledPrices as $scheduledPrice) {
            $schedule = Schedule::findOrFail($scheduledPrice['schedule']);

            //Can improve with saveMany([object,object])
            $product->schedules()->save($schedule, [
                'key' => $scheduledPrice['per'] ?? '',
                'value' => $scheduledPrice['price'] * 100,
            ]);
        }
    }

    /**
     * @param array $productDetails
     */
    private function prepareProductDetails(array $productDetails)
    {
    }

    /**
     * @param $product
     * @return mixed|void
     */
    public function delete($product)
    {
        if (! is_object($product)) {
            $product = Product::findOrFail($product);
        }

        return $product->delete();
    }

    /**
     * Create a new product variation.
     *
     * @param Product $product
     * @return Product
     */
    public function createVariation(Product $product): Product
    {
        //Clone it
        $variation = $product->replicate(['id']);
        $variation->save();

        //Update it's name after save as I don't know which id it is
        $variation->name = trim($variation->name).' '.$variation->id;
        //$variation->save();

        // Add it to the variations table, with stock_from_parent to false
        $product->variations()->save($variation, [
            'stock_from_parent' => false,
        ]);

        return $variation;
    }

    /**
     * @param Product $product
     * @param array $productDetails
     * @return Product
     */
    public function updateVariations(Product $product, array $variations)
    {
        $variations = collect($variations);

        $existingIds = new Collection();
        foreach ($product->variations()->get() as $variation) {
            $existingIds->push($variation->id);
        }

        $incomingIds = new Collection();
        foreach ($variations as $variation) {
            $incomingIds->push($variation['id']);
        }

        $add = $incomingIds->diff($existingIds);
        $update = $existingIds->intersect($incomingIds);
        $remove = $existingIds->diff($incomingIds);

        foreach (['attach' => $add, 'updateExistingPivot' => $update] as $method => $ids) {
            if ($ids->count()) {
                foreach ($ids as $idx => $id) {
                    $variation = $variations->where('id', $id)->first();

                    $product->variations()->$method($id, [
                        'stock_from_parent' => $variation['stock_from_parent'],
                        'is_default' => $variation['is_default'],
                    ]);

                    // Update the product variation's name
                    $variationProduct = Product::find($id);
                    if ($variationProduct->name != $variation['name']) {
                        $variationProduct->update(['name' => $variation['name']]);
                    }
                }
            }
        }

        if ($remove->count()) {
            $product->variations()->detach($remove);
        }
    }
}

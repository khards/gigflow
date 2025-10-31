<?php

namespace App\Booking\Controllers\Frontend;

use App\Booking\Business;
use App\Booking\Contracts\ProductManager as ProductManagerContract;
use App\Booking\Contracts\ScheduleManager as ScheduleManagerContract;
use App\Booking\Product;
use App\Booking\Requests\Frontend\Product\Update;
use App\Domains\Form\Contracts\FormManager as FormManagerContract;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * Class ProductController.
 */
class ProductController extends Controller
{
    /**
     * @var ProductManagerContract
     */
    private $productManager;

    /**
     * @var ScheduleManagerContract
     */
    private $scheduleManager;

    /**
     * @var FormManagerContract
     */
    private $formManager;

    /**
     * ProductController constructor.
     * @param ProductManagerContract $productManager
     * @param ScheduleManagerContract $scheduleManager
     * @param FormManagerContract $formManager
     */
    public function __construct(ProductManagerContract $productManager, ScheduleManagerContract $scheduleManager, FormManagerContract $formManager)
    {
        $this->productManager = $productManager;
        $this->scheduleManager = $scheduleManager;
        $this->formManager = $formManager;
    }

    /**
     * Get all products for product table.
     *
     * @param $businessId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function all($businessId)
    {

        /** @var Business $business */
        $business = Business::findOrFail($businessId);

        if (! $this->authorize('viewAllByBusiness', [Product::class, $business])) {
            return redirect()->route('frontend.user.dashboard')->withFlashDanger(__('Invalid business, please try again.'));
        }

        $products = $this->productManager->raw($business, ['raw']);

        return view('frontend.user.products.products', compact(['business', 'products']));
    }

    /**
     * Return the edit a product screen.
     *
     * @param $productId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit($productId)
    {
        $product = $this->productManager->read($productId);

        if (! $this->authorize('view', $product)) {
            return redirect()->route('frontend.user.dashboard')->withFlashDanger(__('Invalid product, please try again.'));
        }

        $businessId = $product->business->first()->id;

        $schedules = $this->scheduleManager->all($businessId);
        $variations = $product->variations()->get();
        $forms = $this->formManager->all($businessId);

        return view('frontend.user.products.product', compact(['product', 'schedules', 'variations', 'forms']));
    }

    /**
     * Update a product.
     *
     * @param $productId
     * @param Update $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update($productId, Update $request)
    {
        /**
         * @var Product $product
         */
        $product = Product::with('variations')->findOrFail($productId);

        if (! $this->authorize('update', $product)) {
            return redirect()->route('frontend.user.dashboard')->withFlashDanger(__('Invalid product, please try again.'));
        }

        $productData = [];

        if ($request->hasFile('product_image')) {
            $productData['image'] = $request->product_image;
        }
        $productData['form'] = $request->get('form', null);
        $productData['url'] = $request->get('product_url', '');
        $productData['name'] = $request->get('product_name', '');
        $productData['description'] = $request->get('product_description', '');
        $productData['state'] = $request->get('product_state', 'active');
        $productData['type'] = $request->get('type', 'service');
        $productData['is_required'] = $request->get('required', 0);
        $productData['is_addon'] = $request->get('addon', 0);
        $productData['price_type'] = $request->get('price_type', 'fixed');
        $productData['setup_time'] = $request->get('product_setup_time', 0);
        $productData['price_type'] = $request->get('price_type', 'fixed');
        $productData['price_fixed_price'] = $request->get('price_fixed_price', 0);

        $staff_info = $request->get('staff');
        $staff_required = $staff_info['required'] ?? 'no';
        $staff_quantity = $staff_info['quantity'] ?? 0;
        if ($staff_required == 'yes') {
            $productData['staff_quantity'] = $staff_quantity;
        } else {
            $productData['staff_quantity'] = 0;
        }

        $productData['availability_type'] = $request->get('availability_type', 'available');
        $productData['availability_schedule'] = $request->get('availability_schedule', 0);
        $productData['available_quantity'] = $request->get('available_quantity', 0);

        $travelling_info = $request->get('travelling');
        $productData['travelling_limit'] = $travelling_info['limit'] ?? 'no';
        $productData['travelling_value'] = $travelling_info['value'] ?? 0;
        $productData['travelling_type'] = $travelling_info['type'] ?? '';
        $productData['delivery'] = $request->get('delivery');
        $productData['delivery_method'] = Product::calculateDeliveryMethodValue(
            $request->get('delivery_methods', [])
        );

        $productData['advance_charges'] = $request->get('advance_charge');
        $productData['extra_hours_charge_max_hours'] = $request->get('extra_hours_charge_max_hours');
        $productData['block_same_day_bookings'] = $request->get('block_same_day_bookings');
        $productData['block_number_days_future'] = $request->get('block_number_days_future');
        $productData['blocked_postcodes'] = $request->get('blocked_postcodes');


        $specialPriceScheduled = $request->get('special_price_scheduled', []);
        $priceScheduled = $request->get('price_scheduled', []);
        $productData['_scheduled_prices'] = array_merge($specialPriceScheduled, $priceScheduled);

        $this->productManager->update($product, $productData);

        if ($request->get('variations')) {
            $this->productManager->updateVariations($product, $request->get('variations'));
        }

        return redirect()->route('frontend.user.product.edit', $product->id)->withFlashSuccess(__('Product successfully updated.'));
    }

    /**
     * Create a new product.
     *
     * @param $businessId
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create($businessId)
    {
        $business = Business::findOrFail($businessId);

        if (! $this->authorize('create', [Product::class, $business])) {
            return redirect()->route('frontend.user.dashboard')->withFlashDanger(__('Invalid business, please try again.'));
        }

        $product = $this->productManager->create($business, [
            'name' => 'New Product',
            'description' => 'Product description',
            'state' => 'draft',
            'sku' => time().'_'.$business->id,
        ]);

        return redirect()->route('frontend.user.product.edit', $product->id)->withFlashSuccess(__('Product successfully created.'));
    }

    /**
     * Create a product variation - used by product editor.
     *
     * @param Product $product
     * @return array
     */
    public function createVariation(Product $product)
    {
        $variation = $this->productManager->createVariation($product);
        $pivot = DB::table('product_variations')->where('product_id', $variation->id)->first();

        return [
            'id' => $variation->id,
            'name' => $variation->name,
            'stock_from_parent' => (int) $pivot->stock_from_parent,
        ];
    }

    /**
     * Delete product.
     * @param $productId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete($productId)
    {
        $product = Product::findOrFail($productId);
        $businessId = $product->owner_id;

        // Ensure that we 'the business' own
        if (! $this->authorize('delete', $product)) {
            return redirect()->route('frontend.user.dashboard')->withFlashDanger(__('Invalid product, please try again.'));
        }

        // Remove pivot table entry
        $product->parent()->detach();

        // Delete the procut
        $this->productManager->delete($product);

        // Return to the list of products
        return redirect()->route('frontend.user.products.view', $businessId);
    }
}

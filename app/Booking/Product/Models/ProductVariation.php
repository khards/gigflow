<?php

namespace App\Booking\Models;

use App\Booking\Availability\Schedule;
use App\Booking\Availability\Traits\ScheduledAvailable;
use App\Booking\Models\Calendar;
use App\Booking\Product;
use App\Booking\Product\Exceptions\ProductPriceNoncalculable;
use App\Booking\Product\Exceptions\ProductPriceNotAvailable;
use App\Booking\Services\AvailabilityManager;
use App\Booking\Services\DistanceService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\SchemalessAttributes\SchemalessAttributes;
use Vanilo\Framework\Models\Product as VaniloProduct;

/**
 * Class ProductVariation.
 *
 * @property int    $id - Primary key
 * @property int    $product_id - Foreign product_id
 * @property int    $parent_product_id - Parent product id
 * @property int    $stock_from_parent - Product has own stock, or accounts for stock on parent item.
 */
class ProductVariation extends Model
{
    public function product()
    {
        $this->hasOne(Product::class);
    }

    public function parent()
    {
        $this->hasOne(Product::class);
    }
}

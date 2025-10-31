<?php

// TODO -> Move into Availability/models folder
// TODO -> CAN BE DELETED!!!!!!!!! (yes really)

namespace App\Booking\Availability\Models;

use App\Booking\Business;
use App\Booking\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
//Schemaless attributes..
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\SchemalessAttributes\SchemalessAttributes;

class ScheduleMap extends Model
{
    protected $table = 'schedule_maps';

    protected $guarded = ['id'];

    protected $primaryKey = 'schedule_id';

    // Morphs to model (product)

    /*
     * _Many_ X (products) can map _to_ a schedule
     * _Many_ schedules can map _to_ a product
     *
     * https://laravel.com/docs/8.x/eloquent-relationships#many-to-many-polymorphic-relations
     *
        posts               products
        id - integer        id
        name - string       ...

        videos              X (future use)
        id - integer        id
        name - string       ....

        tags                schedules
        id - integer        id
        name - string       .....

        taggables               schedule_maps
        tag_id - integer        schedule_id
        taggable_id - integer   schedule_map_id         // product id
        taggable_type - string  schedule_map_type       // product type
     */

    //This don't do shit.... Model not really needed... HOWEVER the table is used by the relationships on Schedule and Product!

//    public function products() {
//        return $this->morphedByMany(Product::class, 'schedule_map');
//    }
}

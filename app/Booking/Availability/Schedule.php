<?php

// TODO -> Move into Availability/models folder

namespace App\Booking\Availability;

use App\Booking\Business;
use App\Booking\Product;
use App\Scopes\BelongsToBusinessOrUserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
//Schemaless attributes..
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\SchemalessAttributes\SchemalessAttributes;

/**
 * Class Schedule.
 *
 * @property Pivot pivot
 */
class Schedule extends Model
{
    use SoftDeletes;

    protected $table = 'schedules';

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'properties' => 'array',
    ];

    static protected function booted() {
        static::addGlobalScope(new BelongsToBusinessOrUserScope());
    }

    /// schemaless attributes
    public function getPropertiesAttribute(): SchemalessAttributes
    {
        return SchemalessAttributes::createForModel($this, 'properties');
    }

    public function scopeWithProperties(): Builder
    {
        return SchemalessAttributes::scopeWithSchemalessAttributes('properties');
    }

    /**
     * The morphto owner model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model()
    {
        return $this->morphTo();
    }

    // Thuis is product pricing schedule.
    public function products()
    {
        return $this->morphedByMany(Product::class, 'schedule_map');
    }
//    public function business() {
//        return $this->hasOne(Business::class);
//    }
}

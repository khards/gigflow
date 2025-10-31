<?php

namespace App\Booking\Models;

use App\Booking\Holiday;
use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Booking;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Calendar extends Model
{
    protected $guarded = ['id'];

    protected $table = 'calendar';

    /**
     * The quantity of a given model booked between given dates.
     *
     * @param Model $model
     * @param Carbon $startDateTime
     * @param Carbon $finishDateTime
     *
     */
    public static function quantityBookedBetween(Model $model, Carbon $xstartDateTime, Carbon $xfinishDateTime)
    {
        $query = self::where([
            'model_id' => $model->id,
            'model_type' => $model->getMorphClass(),
        ]);

        /*
         * Check for overlapping bookings and return them.
         *
         *  All the ways an event can overlap
         *
         *    *******       ********      *********       **********
         *  SXXXF               SXXXF    SXXXXXXXXXF           SXF
         *
         * 1. X starts before * starts - and X finishes before * finishes - AND X finishes after * starts
         * 2. X starts after  * starts - and X finishes after  * finishes - AND X starts before * finishes
         * 3. X starts before * starts - and X finished after  * finished
         * 4. X starts after  * starts - and X finishes before * finishes
         */
        $query->where(function ($query) use ($xstartDateTime, $xfinishDateTime) {
            $query->where([
                // 1.
                ['start', '<=', (string) $xstartDateTime],
                ['end',   '<=', (string) $xfinishDateTime],
                ['start', '>=', (string) $xfinishDateTime],
            ]);
            $query->orWhere([
                //2.
                ['start', '>=', (string) $xstartDateTime],
                ['end',   '>=', (string) $xfinishDateTime],
                ['end',   '<=', (string) $xstartDateTime],
            ]);
            $query->orWhere([
                //3.
                ['start', '<=', (string) $xstartDateTime],
                ['end',   '>=', (string) $xfinishDateTime]
            ]);
            $query->orWhere([
                //4.
                ['start', '>=', (string) $xstartDateTime],
                ['end',   '<=', (string) $xfinishDateTime]
            ]);
        });

        return $query;
//        $quantityBooked = $query->get()->count();
//
//        //Assumption is that staff can only handle a single booking at the time!
//        return $quantityBooked > 0;
    }

    /**
     * @return MorphTo
     */
    public function booking()
    {
        return $this->morphTo(Booking::class, 'booked_by_type', 'booked_by_id');
    }

//    /**
//     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
//     */
//    public function holidays() {
//        //            hasMany        Holiday's
//        return $this->morphTo(Holiday::class, 'booked_by_type', 'booked_by_id', 'id');
//    }

    /**
     * Booked by relationship.
     *
     * @return MorphTo
     */
    public function booked_by()
    {
        return $this->morphTo();
    }

    /**
     * Model relationship.
     *
     * @return MorphTo
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Staff relationships.
     *
     * @return MorphMany
     */
    public function staff()
    {
        return $this->morphMany(User::class, 'calendar');
    }

    /**
     * Filter to just staff holiday bookings.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeHols($query)
    {
        $morphClass = (app()->make(Holiday::class))->getMorphClass();

        return $query->where('booked_by_type', $morphClass);
    }
}

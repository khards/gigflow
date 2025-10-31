<?php

namespace App\Domains\Order;

use App\Booking\Business;
use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Booking;
use App\Domains\Form\Models\FormResponses;
use App\Domains\Payment\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vanilo\Order\Models\Order as VaniloOrder;
use App\Domains\Order\Models\OrderNotes;

/**
 * @method public static find(int $orderId)
 * @method static find(mixed $orderId)
 *
 * @property mixed $start
 * @property mixed $end
 * @property mixed $location
 */
class Order extends VaniloOrder
{
    use HasFactory;

    /**
     * Get the bookings for this order.
     *
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'order_id', 'id');
    }

    /**
     * Get the Transactions for this order.
     *
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function amountPaid() {
        return $this->transactions->sum('amount');
    }

    public function amountOutstanding() {
        return $this->totalPrice - $this->amountPaid();
    }

    /**
     * Get the owning model.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the owning model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function formResponse(): HasMany
    {
        return $this->hasMany(FormResponses::class);
    }

    public function address() {
        return $this->billpayer->address()->first();
    }

    public function formattedStart() {
        $carbon = new \Carbon\Carbon($this->start);
        $local = $carbon->timezone($this->business->timezone);

        return $local->format('l jS \\of F Y h:i');
    }

    public function formattedEnd() {
        $carbon = new \Carbon\Carbon($this->end);
        $local = $carbon->timezone($this->business->timezone);

        return $local->format('l jS \\of F Y h:i');
    }

    public function notes() : HasMany {
        return $this->hasMany(OrderNotes::class);
    }
}

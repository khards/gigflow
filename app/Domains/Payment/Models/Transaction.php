<?php

namespace App\Domains\Payment\Models;

use App\Casts\Currency;
use App\Domains\Order\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $casts = [
        'details' => 'array',
        'amount' => Currency::class,
    ];

    protected $guarded = [];

    /**
     * Orders.
     *
     * @return BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

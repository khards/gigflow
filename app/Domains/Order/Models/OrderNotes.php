<?php

namespace App\Domains\Order\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderNotes extends Model
{
    protected $guarded = ['id'];

    public function order() : BelongsTo
    {
        return $this->belongsTo(\App\Domains\Order\Order::class);
    }
}

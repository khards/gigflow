<?php

namespace App\Domains\Payment\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $casts = [
        'data' => 'array',
        'business_id' => 'int',
    ];

    protected $guarded = ['id'];

    public function scopeBusiness($query, $id)
    {
        return $query->where('business_id', $id);
    }
}

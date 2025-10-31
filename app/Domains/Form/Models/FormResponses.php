<?php

namespace App\Domains\Form\Models;

use App\Domains\Order\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormResponses extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'form_id',
        'form',
    ];

    protected $casts = [
        'form' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

<?php

namespace App\Booking;

use App\Domains\Auth\Models\User;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Vanilo\Order\Models\OrderProxy;

class Customer extends User
{
    protected $table = 'users';

    protected $attributes = [
        'type' => User::TYPE_CUSTOMER,
    ];

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            $query->where('type', User::TYPE_CUSTOMER);
        });
    }

    /**
     * Get the users orders.
     */
    public function orders()
    {
        return $this->hasMany(OrderProxy::modelClass(), 'user_id', 'id');
    }

    /**
     * The customers that belong to the business. (through bookings) @todo.
     */
    public function businesses(): BelongsToMany
    {
        //return $this->belongsToMany(Business::class);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory(): Factory
    {
        return CustomerFactory::new();
    }
}
